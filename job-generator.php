<?php
/*
Plugin Name: Job Post Generator
Description: A plugin to generate random job posts with categories and custom fields via WP-CLI.
Version: 1.2
Author: Schooner Strategies
*/

if (defined('WP_CLI') && WP_CLI) {
    
    // Register WP-CLI command with the ability to specify the number of posts
    // use like wp generate_job_posts --count=150
    WP_CLI::add_command('generate_job_posts', function($args, $assoc_args) {

        // Load config from config.php
        $config = include plugin_dir_path(__FILE__) . 'job-generator-config.php';

        // Extract arrays from config
        $categories = $config['categories'];
        $employment_types = $config['employment_types'];
        $experience_levels = $config['experience_levels'];
        $companies = $config['companies'];
        $locations = $config['locations'];

        // Number of posts to generate, default to 10 if not specified
        $num_posts = isset($assoc_args['count']) ? intval($assoc_args['count']) : 10;

        // Function to generate a random salary
        function random_salary() {
            return rand(40000, 150000);
        }

        // Function to generate a random date within the last year
        function random_date() {
            return date('Y-m-d H:i:s', strtotime('-' . rand(0, 365) . ' days'));
        }

        // Generate the specified number of job posts
        for ($i = 1; $i <= $num_posts; $i++) {
            // Randomly pick category, employment type, experience level, company, and location
            $category_key = array_rand($categories);
            $category = $category_key;  // Use the key as category name
            $job_titles = $categories[$category_key];  // Get the job titles for that category
            $job_title = $job_titles[array_rand($job_titles)];  // Randomly pick a job title for the category
            $employment_type = $employment_types[array_rand($employment_types)];
            $experience_level = $experience_levels[array_rand($experience_levels)];
            $company = $companies[array_rand($companies)];
            $location = $locations[array_rand($locations)];

            // Generate random salary and date
            $salary = random_salary();
            $date_created = random_date();

            // Create a new post
            $new_post = [
                'post_title'    => "$job_title at $company",
                'post_content'  => "This is a job posting for the position of $job_title in $category at $company, located in $location.",
                'post_status'   => 'publish',
                'post_author'   => 1,
                'post_category' => [wp_create_category($category)], // Create category if it doesn't exist
                'post_date'     => $date_created,
                'post_type'     => 'post'
            ];
            $post_id = wp_insert_post($new_post);

            // Check if update_field (ACF) exists, otherwise use update_post_meta
            if (function_exists('update_field')) {
                update_field('salary', $salary, $post_id);
                update_field('employment_type', $employment_type, $post_id);
                update_field('experience_level', $experience_level, $post_id);
                update_field('company', $company, $post_id);
                update_field('location', $location, $post_id);
            } else {
                // Use update_post_meta as fallback
                update_post_meta($post_id, 'salary', $salary);
                update_post_meta($post_id, 'employment_type', $employment_type);
                update_post_meta($post_id, 'experience_level', $experience_level);
                update_post_meta($post_id, 'company', $company);
                update_post_meta($post_id, 'location', $location);
            }

            WP_CLI::success("Created post ID $post_id with title '$job_title at $company'");
        }

    }, [
        'shortdesc' => 'Generate random job posts with custom fields.',
        'synopsis' => [
            [
                'type'     => 'assoc',
                'name'     => 'count',
                'description' => 'The number of posts to generate.',
                'optional' => true,
                'default'  => 10
            ]
        ]
    ]);

}
