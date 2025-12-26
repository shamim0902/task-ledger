<?php
namespace TaskLedger\App\Hooks\CLI;

use TaskLedger\App\App;
use TaskLedger\Database\DBMigrator;

class Commands
{
    public function migrate_fresh($args, $assoc_args, $checkDev = true)
    {
        if ($checkDev && App::make('config')->get('app.env') !== 'dev') {
            if (class_exists('WP_CLI')) {
                echo \WP_CLI::colorize('%yYou Are Not In Dev Mode');
            } else {
                echo "You Are Not In Dev Mode";
            }
            return;
        }
        global $wpdb;
        $wpdb->query("SET GLOBAL FOREIGN_KEY_CHECKS=0;");

        try {
            DBMigrator::migrateDown();
            DBMigrator::migrateUp();

        } catch (\Exception $e) {
            if (class_exists('WP_CLI')) {
                echo \WP_CLI::colorize('%r' . $e->getMessage() . '%n');
            } else {
                echo $e->getMessage();
            }
        }

        $wpdb->query("SET GLOBAL FOREIGN_KEY_CHECKS=0;");

        if (isset($assoc_args['seed'])) {
            $this->seed_all($args, $assoc_args, 1000, false);
        }

        if (class_exists('WP_CLI')) {
            \WP_CLI::line('All Done!');
        } else {
            echo "All Done!";
        }
    }

    // public function seed_all($args, $assoc_args, $default = 100, $checkDev = true)
    // {
    //     if ($checkDev) {
    //         $this->authorize();
    //     }

    //     $count = isset($assoc_args['count']) ? absint($assoc_args['count']) : $default;
    //     if (class_exists('WP_CLI')) {
    //         echo \WP_CLI::colorize('%yInserting ' . $count . ' records. Please wait...%n');
    //     }
    //     DBSeeder::run($count);
    //     if (class_exists('WP_CLI')) {
    //         echo \WP_CLI::colorize("%GSuccess: $count records inserted into the database.%n");
    //     }
    // }

    public function authorize($checkDev = true)
    {
        if ($checkDev && App::make('config')->get('app.env') !== 'dev') {
            if (class_exists('WP_CLI')) {
                echo \WP_CLI::colorize('%yYou Are Not In Dev Mode');
            } else {
                echo('You Are Not In Dev Mode');
            }
            die();
        }
    }
}
