<?php

$REGISTER_LTI2 = array(
"name" => "Simple Attendance Tool",
"FontAwesome" => "fa-server",
"short_name" => "Attendance Tool",
"description" => "This is a simple attendance tool that allows the instructor to set a code and the students enter the code.",
    // By default, accept launch messages..
    "messages" => array("launch"),
    "privacy_level" => "name_only",  // anonymous, name_only, public
    "license" => "Apache",
    "languages" => array(
        "English", "Spanish"
    ),
    "source_url" => "https://github.com/tsugitools/attend",
    // For now Tsugi tools delegate this to /lti/store
    "placements" => array(
        /*
        "course_navigation", "homework_submission",
        "course_home_submission", "editor_button",
        "link_selection", "migration_selection", "resource_selection",
        "tool_configuration", "user_navigation"
        */
    ),
    "screen_shots" => array(
        "store/screen-01.png",
        "store/screen-02.png",
        "store/screen-03.png"
    )

);
