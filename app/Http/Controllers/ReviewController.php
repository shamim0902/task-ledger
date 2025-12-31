<?php

namespace TaskLedger\App\Http\Controllers;

use TaskLedger\App\Models\Log;
use TaskLedger\App\Models\LogItem;
use TaskLedger\App\Models\User;
use TaskLedger\App\Models\Role;
use TaskLedger\App\Models\UserRoleProject;
use TaskLedger\App\Models\ManagerMember;
use TaskLedger\App\Services\PermissionService;
use TaskLedger\Framework\Http\Request\Request;

class ReviewController extends Controller
{
    /**
     * Get reviewable members (manager: assigned only)
     */
    public function getReviewableMembers()
    {
        try {
            $userId = get_current_user_id();
            
            if (!$userId || $userId === 0) {
                return $this->sendError(['message' => 'User not authenticated'], 401);
            }
            
            // Admin and Manager only
            $isAdmin = PermissionService::isAdmin($userId);
            $isManager = PermissionService::isManager($userId);
            
            if (!$isAdmin && !$isManager) {
                return $this->sendError(['message' => 'You do not have permission to review tasks'], 403);
            }
            
            if ($isAdmin) {
                // Admin can review ALL users - get all users who have submitted logs
                $userIdsWithLogs = Log::distinct()
                    ->pluck('user_id')
                    ->toArray();
                
                if (empty($userIdsWithLogs)) {
                    return [];
                }
                
                $members = User::whereIn('ID', $userIdsWithLogs)->get();
            } else {
                // Manager can only review assigned members
                // Get member IDs directly from ManagerMember table, then fetch users
                $memberIds = ManagerMember::where('manager_id', $userId)
                    ->pluck('member_id')
                    ->toArray();
                
                if (empty($memberIds)) {
                    // Return empty array if manager has no assigned members
                    return [];
                }
                
                $members = User::whereIn('ID', $memberIds)->get();
                
                // If no users found (maybe users were deleted), return empty array
                if ($members->isEmpty()) {
                    return [];
                }
            }

            // Get unread submission counts for each member
            // Filter out any null members (in case user was deleted but assignment remains)
            $membersWithCounts = $members->filter(function($member) {
                return $member !== null && isset($member->ID);
            })->map(function($member) {
                try {
                    // Include NULL values as unreviewed (for existing records before review feature)
                    $unreadCount = Log::where('user_id', $member->ID)
                        ->where(function($q) {
                            $q->where('reviewed', false)
                              ->orWhereNull('reviewed');
                        })
                        ->count();
                    
                    return [
                        'id' => $member->ID,
                        'name' => $member->display_name ?? $member->user_nicename ?? 'Unknown',
                        'email' => $member->user_email ?? '',
                        'initials' => $this->getInitials($member->display_name ?? $member->user_nicename ?? 'U'),
                        'unread_count' => $unreadCount,
                    ];
                } catch (\Exception $e) {
                    // Skip this member if there's an error processing it
                    return null;
                }
            })->filter(function($member) {
                return $member !== null;
            });

            // Return as array for consistent JSON response
            return $membersWithCounts->values()->toArray();
        } catch (\Exception $e) {
            return $this->sendError(['message' => 'Failed to load members: ' . $e->getMessage()], 500);
        } catch (\Error $e) {
            return $this->sendError(['message' => 'Failed to load members: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get submissions with filters
     */
    public function getSubmissions(Request $request)
    {
        $userId = get_current_user_id();
        // Admin and Manager only
        if (!PermissionService::isAdmin($userId) && !PermissionService::isManager($userId)) {
            return $this->sendError('You do not have permission to review tasks', 403);
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $memberId = $request->get('member_id');
        $reviewed = $request->get('reviewed'); // 'all', 'reviewed', 'unreviewed'
        $page = (int)$request->get('page', 1);
        $perPage = (int)$request->get('per_page', 20);

        // Get reviewable member IDs
        $isAdmin = PermissionService::isAdmin($userId);
        $isManager = PermissionService::isManager($userId);
        
        if ($isAdmin) {
            // Admin can review ALL users - no filter needed
            $reviewableMemberIds = null; // null means all users
        } else {
            // Manager can only review assigned members
            // Get member IDs directly from ManagerMember table
            $reviewableMemberIds = ManagerMember::where('manager_id', $userId)
                ->pluck('member_id')
                ->toArray();
            
            // If no reviewable members, return empty result
            if (empty($reviewableMemberIds)) {
                return [
                    'submissions' => [],
                    'current_page' => (int)$page,
                    'per_page' => (int)$perPage,
                    'total' => 0,
                    'total_pages' => 0,
                ];
            }
        }

        // Build query
        $query = Log::with(['user', 'logItems'])
            ->orderBy('log_date', 'desc')
            ->orderBy('created_at', 'desc');
        
        // Only filter by user_id if not admin (admin sees all)
        if ($reviewableMemberIds !== null) {
            $query->whereIn('user_id', $reviewableMemberIds);
        }
        

        // Filter by member (verify member is in reviewable list for managers)
        if ($memberId) {
            if ($isAdmin) {
                // Admin can review any user
                $query->where('user_id', $memberId);
            } else {
                // Manager can only review assigned members
                if (!in_array($memberId, $reviewableMemberIds)) {
                    return $this->sendError('You do not have permission to review this member', 403);
                }
                $query->where('user_id', $memberId);
            }
        }

        // Filter by date range (only if provided and not empty)
        if (!empty($startDate) && is_string($startDate)) {
            // Validate date format (YYYY-MM-DD)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
                $query->where('log_date', '>=', $startDate);
            }
        }
        if (!empty($endDate) && is_string($endDate)) {
            // Validate date format (YYYY-MM-DD)
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
                $query->where('log_date', '<=', $endDate);
            }
        }

        // Filter by reviewed status
        if ($reviewed === 'reviewed') {
            $query->where('reviewed', true);
        } elseif ($reviewed === 'unreviewed') {
            // Include NULL values as unreviewed (for existing records before review feature)
            $query->where(function($q) {
                $q->where('reviewed', false)
                  ->orWhereNull('reviewed');
            });
        }
        // If 'all', don't filter by reviewed status

        // Pagination
        $total = $query->count();
        
        $logs = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();
        

        // Format response
        $submissions = $logs->map(function($log) {
            $taskCount = $log->logItems->count();
            $reviewedTaskCount = $log->logItems->where('reviewed', true)->count();
            
            return [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'user_name' => $log->user->display_name ?? $log->user->user_nicename,
                'user_email' => $log->user->user_email,
                'user_initials' => $this->getInitials($log->user->display_name ?? $log->user->user_nicename),
                'log_date' => $log->log_date,
                'additional_notes' => $log->additional_notes,
                'reviewed' => (bool)$log->reviewed,
                'reviewed_at' => $log->reviewed_at,
                'reviewed_by' => $log->reviewed_by,
                'task_count' => $taskCount,
                'reviewed_task_count' => $reviewedTaskCount,
                'created_at' => $log->created_at,
            ];
        });

        return [
            'submissions' => $submissions,
            'current_page' => (int)$page,
            'per_page' => (int)$perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
        ];
    }

    /**
     * Get submission details with all tasks
     */
    public function getSubmissionDetails($logId)
    {
        $userId = get_current_user_id();
        
        // Admin and Manager only
        if (!PermissionService::isAdmin($userId) && !PermissionService::isManager($userId)) {
            return $this->sendError('You do not have permission to review tasks', 403);
        }

        $log = Log::with(['user', 'logItems'])->findOrFail($logId);

        // Check if user can review this member
        if (PermissionService::isAdmin($userId)) {
            // Admin can review all
        } else {
            // Manager can only review assigned members
            $canReview = PermissionService::canManageUser($userId, $log->user_id);
            if (!$canReview) {
                return $this->sendError('You do not have permission to review this submission', 403);
            }
        }

        // Get task details for each log item
        $tasks = $log->logItems->map(function($item) {
            $taskTitle = 'Task #' . $item->task_id;
            
            // Try to get task details from Fluent Boards if available
            if (class_exists('\FluentBoards\App\Models\Task')) {
                $task = \FluentBoards\App\Models\Task::find($item->task_id);
                if ($task) {
                    $taskTitle = $task->title;
                } else {
                    $taskTitle = $item->note ?: 'Task not found';
                }
            } else {
                // Fluent Boards not installed, use fallback
                $taskTitle = $item->note ?: 'Custom Task #' . $item->task_id;
            }
            
            return [
                'id' => $item->id,
                'log_id' => $item->log_id,
                'task_id' => $item->task_id,
                'task_title' => $taskTitle,
                'activity_type' => $item->activity_type,
                'complete_weight' => $item->complete_weight,
                'time_spent' => $item->time_spent,
                'note' => $item->note,
                'block_reason' => $item->block_reason,
                'reviewed' => (bool)$item->reviewed,
                'reviewed_at' => $item->reviewed_at,
                'reviewed_by' => $item->reviewed_by,
            ];
        });

        return [
            'id' => $log->id,
            'user_id' => $log->user_id,
            'user_name' => $log->user->display_name ?? $log->user->user_nicename,
            'user_email' => $log->user->user_email,
            'user_initials' => $this->getInitials($log->user->display_name ?? $log->user->user_nicename),
            'log_date' => $log->log_date,
            'additional_notes' => $log->additional_notes,
            'reviewed' => (bool)$log->reviewed,
            'reviewed_at' => $log->reviewed_at,
            'reviewed_by' => $log->reviewed_by,
            'tasks' => $tasks,
            'created_at' => $log->created_at,
        ];
    }

    /**
     * Mark log as reviewed
     */
    public function markLogReviewed(Request $request, $logId)
    {
        $userId = get_current_user_id();
        
        // Admin and Manager only
        if (!PermissionService::isAdmin($userId) && !PermissionService::isManager($userId)) {
            return $this->sendError('You do not have permission to review tasks', 403);
        }

        $log = Log::findOrFail($logId);

        // Check if user can review this member
        if (PermissionService::isAdmin($userId)) {
            // Admin can review all
        } else {
            // Manager can only review assigned members
            $canReview = PermissionService::canManageUser($userId, $log->user_id);
            if (!$canReview) {
                return $this->sendError('You do not have permission to review this submission', 403);
            }
        }

        $log->update([
            'reviewed' => true,
            'reviewed_at' => current_time('mysql'),
            'reviewed_by' => $userId,
        ]);

        return [
            'message' => 'Submission marked as reviewed',
            'log' => $log->fresh(),
        ];
    }

    /**
     * Mark log item as reviewed
     */
    public function markLogItemReviewed(Request $request, $logItemId)
    {
        $userId = get_current_user_id();
        
        // Admin and Manager only
        if (!PermissionService::isAdmin($userId) && !PermissionService::isManager($userId)) {
            return $this->sendError('You do not have permission to review tasks', 403);
        }

        $logItem = LogItem::with('log')->findOrFail($logItemId);
        $log = $logItem->log;

        // Check if user can review this member
        if (PermissionService::isAdmin($userId)) {
            // Admin can review all
        } else {
            // Manager can only review assigned members
            $canReview = PermissionService::canManageUser($userId, $log->user_id);
            if (!$canReview) {
                return $this->sendError('You do not have permission to review this task', 403);
            }
        }

        $logItem->update([
            'reviewed' => true,
            'reviewed_at' => current_time('mysql'),
            'reviewed_by' => $userId,
        ]);

        // Check if all tasks in the log are reviewed, if so mark log as reviewed
        $allReviewed = LogItem::where('log_id', $log->id)
            ->where('reviewed', false)
            ->count() === 0;

        if ($allReviewed) {
            $log->update([
                'reviewed' => true,
                'reviewed_at' => current_time('mysql'),
                'reviewed_by' => $userId,
            ]);
        }

        return [
            'message' => 'Task marked as reviewed',
            'log_item' => $logItem->fresh(),
            'log_reviewed' => $allReviewed,
        ];
    }

    /**
     * Bulk mark as reviewed
     */
    public function bulkMarkReviewed(Request $request)
    {
        $userId = get_current_user_id();
        
        // Admin and Manager only
        if (!PermissionService::isAdmin($userId) && !PermissionService::isManager($userId)) {
            return $this->sendError('You do not have permission to review tasks', 403);
        }

        $request->validate([
            'log_ids' => 'nullable|array',
            'log_item_ids' => 'nullable|array',
        ]);

        $logIds = $request->get('log_ids', []);
        $logItemIds = $request->get('log_item_ids', []);

        $reviewedLogs = 0;
        $reviewedItems = 0;

        // Review logs
        if (!empty($logIds)) {
            $logs = Log::whereIn('id', $logIds)->get();
            
            foreach ($logs as $log) {
                // Check if user can review this member
                if (PermissionService::isAdmin($userId)) {
                    // Admin can review all
                } else {
                    // Manager can only review assigned members
                    $canReview = PermissionService::canManageUser($userId, $log->user_id);
                    if (!$canReview) {
                        continue;
                    }
                }

                $log->update([
                    'reviewed' => true,
                    'reviewed_at' => current_time('mysql'),
                    'reviewed_by' => $userId,
                ]);
                $reviewedLogs++;
            }
        }

        // Review log items
        if (!empty($logItemIds)) {
            $logItems = LogItem::with('log')->whereIn('id', $logItemIds)->get();
            
            foreach ($logItems as $logItem) {
                $log = $logItem->log;
                
                // Check if user can review this member
                if (PermissionService::isAdmin($userId)) {
                    // Admin can review all
                } else {
                    // Manager can only review assigned members
                    $canReview = PermissionService::canManageUser($userId, $log->user_id);
                    if (!$canReview) {
                        continue;
                    }
                }

                $logItem->update([
                    'reviewed' => true,
                    'reviewed_at' => current_time('mysql'),
                    'reviewed_by' => $userId,
                ]);
                $reviewedItems++;

                // Check if all tasks in the log are reviewed
                $allReviewed = LogItem::where('log_id', $log->id)
                    ->where('reviewed', false)
                    ->count() === 0;

                if ($allReviewed) {
                    $log->update([
                        'reviewed' => true,
                        'reviewed_at' => current_time('mysql'),
                        'reviewed_by' => $userId,
                    ]);
                }
            }
        }

        return [
            'message' => 'Items marked as reviewed',
            'reviewed_logs' => $reviewedLogs,
            'reviewed_items' => $reviewedItems,
        ];
    }

    /**
     * Helper to get user initials
     */
    private function getInitials($name)
    {
        $words = explode(' ', trim($name));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[count($words) - 1], 0, 1));
        }
        return strtoupper(substr($name, 0, 2));
    }
}

