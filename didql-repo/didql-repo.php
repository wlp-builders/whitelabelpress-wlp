<?php
/*
Plugin Name: DidQL Repo
Description: Decentralized Plugin repo
Version: 1.0
Author: Neil
License: Spirit of Time 1.0
*/


// Exit if accessed directly
if (!defined('ABSPATH')) exit;

require_once __DIR__.'/lib/php-searchPluginsTxt.php';

global $didql;
if($didql) {
   $didql->addRoute('did_user', [
        'repo__search' => function ($user, $keyword) { // subrepo = stable, hatchery, hatchery-unverified
            if(function_exists('log_message')) {
                log_message('didql > search');
                log_message('did_user:user | data: '.json_encode($user));
                log_message('did_user:update() called | data: '.json_encode(["subrepo"=>$subrepo]));
            }
            
            $filePath = '/home/neil/yoga/repos/plugins-txt-repo/data.json';
$results = searchPluginsTxt($filePath, $keyword);


            return [$results, 200];
        }
    ]);
    $didql->addRoute('did_user', [
        'repo__update' => function ($user, $subrepo) { // subrepo = stable, hatchery, hatchery-unverified
            if(function_exists('log_message')) {
                log_message('didql > repo__update');
                log_message('did_user:user | data: '.json_encode($user));
                log_message('did_user:update() called | data: '.json_encode(["subrepo"=>$subrepo]));
            }

            return ['todo send list stable packages', 201];
        }
    ]);
    $didql->addRoute('did_user', [
        'repo__downloadOne' => function ($user, $subrepo, $package) { // subrepo = stable, hatchery, hatchery-unverified
            if(function_exists('log_message')) {
                log_message('didql > repo__downloadOne');
                log_message('did_user:user | data: '.json_encode($user));
                log_message('did_user:downloadOne() called | data: '.json_encode(["subrepo"=>$subrepo]));
            }
            
            return ['todo tmp url/raw data download packages', 201];
        }
    ]);
} else {
    var_dump('didql not loaded correctly ('.$didql.')');
}

