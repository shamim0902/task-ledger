<?php

namespace TaskLedger\App\Http\Controllers;

use FluentBoards\App\Models\Board;
use FluentBoards\App\Services\BoardService;
use FluentBoards\App\Services\StageService;
use FluentBoards\App\Services\LabelService;
use TaskLedger\App\Models\User;
use TaskLedger\Framework\Http\Request\Request;

class ProjectsController extends Controller
{
    private $boardService;
    private $stageService;
    private $labelService;

    public function __construct()
    {
        parent::__construct();
        $this->boardService = new BoardService();
        $this->stageService = new StageService();
        $this->labelService = new LabelService();
    }

    /**
     * Get all boards from fluent-boards
     */
    public function index(Request $request)
    {
        $userId = get_current_user_id();
        $search = $request->getSafe('search', 'sanitize_text_field', '');
        $per_page = $request->getSafe('per_page', 'intval', 20);
        $page = $request->getSafe('page', 'intval', 1);

        $query = Board::whereNull('archived_at')
            ->where('type', 'to-do')
            ->byAccessUser($userId);

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        $boards = $query->orderBy('created_at', 'DESC')
            ->with('stages', 'users')
            ->paginate($per_page, ['*'], 'page', $page);

        return [
            'boards' => $boards,
        ];
    }

    /**
     * Create a new board/project
     */
    public function create(Request $request)
    {
        $data = $request->get('board', []);

        // Validate required fields
        if (empty($data['title'])) {
            return $this->sendError('Board title is required', 400);
        }

        $boardData = [
            'title' => sanitize_text_field($data['title']),
            'description' => isset($data['description']) ? sanitize_textarea_field($data['description']) : null,
            'type' => 'to-do',
            'currency' => isset($data['currency']) ? sanitize_text_field($data['currency']) : null,
        ];

        try {
            $board = $this->boardService->createBoard($boardData);
            $this->labelService->createDefaultLabel($board->id);
            $this->stageService->createDefaultStages($board);

            return [
                'message' => __('Project has been created successfully', 'taskledger'),
                'board' => $board,
            ];
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    /**
     * Import boards from fluent-boards
     */
    public function import(Request $request)
    {
        $boardIds = $request->get('board_ids', []);

        if (empty($boardIds) || !is_array($boardIds)) {
            return $this->sendError('Please select at least one board to import', 400);
        }

        $userId = get_current_user_id();
        $imported = [];
        $errors = [];

        foreach ($boardIds as $boardId) {
            $boardId = (int) $boardId;
            $board = Board::find($boardId);

            if (!$board) {
                $errors[] = "Board #{$boardId} not found";
                continue;
            }

            // Check if user has access to this board
            $userBoards = Board::byAccessUser($userId)->pluck('id')->toArray();
            if (!in_array($boardId, $userBoards)) {
                $errors[] = "You don't have access to board: {$board->title}";
                continue;
            }

            // Board is already accessible, so we just mark it as imported
            // In a real scenario, you might want to copy it or mark it differently
            $imported[] = [
                'id' => $board->id,
                'title' => $board->title,
            ];
        }

        return [
            'message' => count($imported) . ' project(s) imported successfully',
            'imported' => $imported,
            'errors' => $errors,
        ];
    }

    /**
     * Get a single board by ID
     */
    public function show(Request $request, $id)
    {
        $userId = get_current_user_id();
        $board = Board::where('id', $id)
            ->whereNull('archived_at')
            ->byAccessUser($userId)
            ->with('stages', 'users', 'labels')
            ->first();

        if (!$board) {
            return $this->sendError('Board not found', 404);
        }

        return [
            'board' => $board,
        ];
    }

    /**
     * Get roles and users for a specific board/project
     */
    public function getRoles(Request $request, $id)
    {
        $userId = get_current_user_id();
        $board = Board::where('id', $id)
            ->whereNull('archived_at')
            ->byAccessUser($userId)
            ->with('stages', 'users', 'labels')
            ->first();

        if (!$board) {
            return $this->sendError('Board not found', 404);
        }

        // Get all role assignments for this board
        $assignments = \TaskLedger\App\Models\UserRoleProject::where('board_id', $id)
            ->with(['role', 'user'])
            ->get()
            ->map(function($assignment) {
                return [
                    'id' => $assignment->id,
                    'user_id' => $assignment->user_id,
                    'role_id' => $assignment->role_id,
                    'board_id' => $assignment->board_id,
                    'role' => $assignment->role,
                    'user' => $assignment->user,
                ];
            });

        // Get all available roles
        $roles = \TaskLedger\App\Models\Role::all();

        // Get all WordPress users
        $wpUsers = get_users(['fields' => ['ID', 'user_login', 'user_email', 'display_name', 'user_nicename']]);
        $users = array_map(function($user) {
            return [
                'ID' => $user->ID,
                'id' => $user->ID,
                'user_login' => $user->user_login,
                'user_email' => $user->user_email,
                'display_name' => $user->display_name,
                'user_nicename' => $user->user_nicename,
            ];
        }, $wpUsers);

        return [
            'board' => $board,
            'assignments' => $assignments,
            'roles' => $roles,
            'users' => $users,
        ];
    }
}

