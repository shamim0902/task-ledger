<?php

// Define the main/primary menu bar
$router->menu('primary', function($router) {
    $router->add('/', 'modules/dashboard')
        ->name('dashboard')
        ->icon('HomeFilled')
        ->title(__('Dashboard', 'wpfluent'))
        ->props([
            'user'     => wp_get_current_user(),
            'isAdmin'  => current_user_can('manage_options'),
        ])
        ->middleware('auth');

    $router->add('/pm', 'modules/pm/PMDashboard')
        ->name('pm.dashboard')
        ->icon('DataAnalysis')
        ->title(__('PM Dashboard', 'taskledger'))
        ->props([
            'user'     => wp_get_current_user(),
            'isAdmin'  => current_user_can('manage_options'),
        ])
        ->middleware('auth');

    $router->add('/roles', 'modules/roles')
        ->name('roles')
        ->icon('UserFilled')
        ->title(__('Roles', 'taskledger'))
        ->props([
            'user'     => wp_get_current_user(),
            'isAdmin'  => current_user_can('manage_options'),
        ])
        ->middleware('auth');

    $router->add('/review', 'modules/review/ReviewDashboard')
        ->name('review')
        ->icon('DocumentChecked')
        ->title(__('Review Tasks', 'taskledger'))
        ->props([
            'user'     => wp_get_current_user(),
            'isAdmin'  => current_user_can('manage_options'),
        ])
        ->middleware('auth');

    $router->add('posts-all', 'modules/posts')
        ->name('posts.all')
        ->icon('Memo')
        ->title(__('Posts', 'wpfluent'));

    $router->add('/users', 'modules/users')
        ->name('users')
        ->icon('User')
        ->title(__('Users', 'wpfluent'))
        ->middleware(['auth'])
        ->children(function($router) {
            $router->add(
                ':id/view',
                'modules/users/components/view',
                'users.view'
            )
            ->meta([
                'middleware' => current_user_can('administrator')
                    ? ['auth', 'admin'] : ['auth']
            ]);
        });
});

// Define a secondary menu bar
$router->menu('secondary', function($router) {
    $router->group([
        'label' => __('Posts', 'wpfluent'),
        'icon'  => 'Aim',
    ], function ($router) {
        
        $router->use('posts.all')
            ->icon('Share')
            ->title(__('All', 'wpfluent'));

        $router->group([
            'label' => __('Filter', 'wpfluent'),
            'icon'  => 'Filter',
        ], function ($submenu) {
            
            $submenu->add('posts-draft', 'modules/posts/draft')
                ->name('posts.draft')
                ->title(__('Drafts', 'wpfluent'))
                ->icon('Setting')
                ->children(function($router) {
                    $router->add(
                        ':id',
                        'modules/posts/detail',
                    )
                    ->name('posts.draft.detail')
                    ->child(
                        'child',
                        'modules/posts/dChild',
                        'posts.draft.detail.child'
                    )
                    ->name('posts.draft.detail.child');
                });

            $submenu->add('posts-publish', 'modules/posts/publish')
                ->name('posts.publish')
                ->icon('Edit')
                ->title(__('Published', 'wpfluent'));

            $submenu->group([
                'label' => __('More Filter', 'wpfluent'),
                'icon'  => 'ZoomIn',
            ], function ($submenu) {
                $submenu->add('posts-trash', 'modules/posts/trash')
                    ->name('posts.trash')
                    ->icon('Delete')
                    ->title(__('Trash', 'wpfluent'));

                $submenu->group([
                    'label' => __('More Private Filter', 'wpfluent'),
                    'icon'  => 'More',
                ], function ($submenu) {
                        $submenu->add('posts-private', 'modules/posts/private')
                            ->name('posts.private')
                            ->icon('Lock')
                            ->title(__('Private', 'wpfluent'));
                    });
            });

            $submenu->group([
                'label' => __('More Pending Filter', 'wpfluent'),
                'icon'  => 'Promotion',
            ], function ($submenu) {
                    $submenu->add('posts-pending', 'modules/posts/pending')
                        ->name('posts.pending')
                        ->icon('Clock')
                        ->title(__('Pending Posts', 'wpfluent'));
                });
        });
    });
});

// Define a footer menu bar
$router->menu('footer', function ($router) {
    $router->group([
        'label' => __('Footer Panel', 'fluentdoctest'),
        'name'  => 'footer',
    ], function($router) {
        $router->add('users', 'modules/users')->label(
            __('Users', 'fluentdoctest')
        );

        $router->group([
            'label' => __('Nested Footer Panel', 'fluentdoctest'),
            'name'  => 'nestedfooter',
        ], function($router) {
            $router->add('users', 'modules/users')->label(
                __('Users', 'fluentdoctest')
            );
        });
    });

    $router->use('posts.all')
        ->icon(null)
        ->title(__('All Posts', 'fluentdoctest'));

    $router->use('posts.draft')
        ->icon(null)
        ->title(__('All Drafts', 'fluentdoctest'));

    $router->use('posts.pending')
        ->icon(null)
        ->title(__('All Pending', 'fluentdoctest'));

    $router->use('posts.publish')
        ->icon(null)
        ->title(__('All Published', 'fluentdoctest'));

    $router->use('posts.trash')
        ->icon(null)
        ->title(__('All Trashed', 'fluentdoctest'));
});

// Define Additional menu items
// $item = $router->footer->submenu('footer')->submenu('nestedfooter');
// $item = $router->footer->submenu('footer');
// $item = $router->primary;

// Example conditional menu items (commented out - requires app instance)
// $item->addIf('posts', 'modules/posts', fn() => current_user_can('manage_options'))
//     ->name('posts')
//     ->label(
//         __('Posts...', 'fluentdoctest')
//     );

// $item->addIf('media', 'modules/media', current_user_can('manage_options'))
//     ->name('media')
//     ->title('Media')
//     ->enqueue(function() {
//         wp_enqueue_media();
//     });
