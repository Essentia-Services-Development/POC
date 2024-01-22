<?php

/**
 * The functions in this file are for working with plugin settings and user settings. We have here functions for creating
 * Settings, changing them, getting their values, and even for displaying form elements for a plugin settings page.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * If the plugin setting does not exist, it creates it, if it does exist it updates it, and retuns true on success, and false on failure
 * @param string $name
 * @param mixed $value
 * @return bool
 */
function be_mu_set_or_make_setting( $name, $value ) {

    // The current value of the setting (false if it does not exist)
    $current_value = get_site_option( $name );

    // If the setting does not exist we create it and return the status
    if ( false === $current_value ) {
        return add_site_option( $name, $value );

    // The current value is the same as the new one, no need to do anything so we return true
    } elseif ( $current_value == $value ) {
        return true;

    // We need to update the setting so we do and return the status
    } else {

        /**
         * Instead of just updating with update_site_option, we will first delete the option and then add it. We do this because in some situations
         * WordPress makes many copies of the same option (might be a bug in WP core, I am not sure) and this could cause problems. Deleting the option
         * deletes all copies so we are now sure it is just one.
         */
        delete_site_option( $name );
        return add_site_option( $name, $value );
    }
}

/**
 * Updates or creates (if they do not exist) multiple plugin settings.
 * It takes an array as an argument, where the array keys are the names, and the array values are the values.
 * @param array $settings
 * @return bool
 */
function be_mu_set_or_make_settings( $settings ) {

    // Return false if $settings is not an array
    if ( ! is_array( $settings ) ) {
        return false;
    }

    // Loop through each setting in the array
    foreach ( $settings as $name => $value ) {

        // If at any point there is an error while updating, we abort everything and return false
        if ( be_mu_set_or_make_setting( $name, $value ) === false ) {
            return false;
        }
    }

    // If everything went good, we return true
    return true;
}

/**
 * Creates a plugin setting only if it does not exist (does not change it if exists), and retuns true on success, and false on failure.
 * It takes an array as an argument, where the array keys are the names, and the array values are the values.
 * @param string $name
 * @param mixed $value
 * @return bool
 */
function be_mu_make_setting( $name, $value ) {
    if ( get_site_option( $name ) === false ) {
        return add_site_option( $name, $value );
    }

    // We consider success if we did nothing, since there were no errors
    return true;
}

/**
 * Creates multiple plugin settings only if they do not exist (does not change them if they exists).
 * It takes an array as an argument, where the array keys are the names, and the array values are the values.
 * @param array $settings
 * @return bool
 */
function be_mu_make_settings( $settings ) {

    // Return false if $settings is not an array
    if ( ! is_array( $settings ) ) {
        return false;
    }

    // Loop through each setting in the array
    foreach ( $settings as $name => $value ) {

        // If at any point there is an error while updating, we abort everything and return false
        if ( be_mu_make_setting( $name, $value ) === false) {
            return false;
        }
    }

    // If everything went good, we return true
    return true;
}

/**
 * Delete a setting.
 * We don't really need this function (we can use delete_site_option instead), but for consistency with the other ones (and avoid mistakes), let's make it.
 * @param string $name
 * @return bool
 */
function be_mu_delete_setting( $name ) {
    return delete_site_option( $name );
}

/**
 * Get a setting value based on a setting name; the default value will be returned id there are no results.
 * We don't really need this function (we can use get_site_option instead), but for consistency with the other ones (and avoid mistakes), let's make it.
 * @param string $name
 * @param mixed $default_value
 * @return bool
 */
function be_mu_get_setting( $name, $default_value = false ) {
    return get_site_option( $name, $default_value );
}

/**
 * Returns an array of setting values based on an array of setting names. The array keys in the reuturned array are the setting names
 * @param array $names
 * @return mixed
 */
function be_mu_get_settings( $names ) {

    // Return false if $names is not an array
    if ( ! is_array( $names ) ) {
        return false;
    }

    // We create an array that will hold the results aranged in the way that we want (the name is the key and the value is the value)
    $a_results = Array();

    // We get the settings add them to the array
    foreach ( $names as $current_name ) {
        $a_results[ $current_name ] = be_mu_get_setting( $current_name );
    }

    // We return the array with the settings
    return $a_results;
}

/**
 * Display an html select form element to use in a plugin settings page. It is automatically set to the current setting value
 * @param string $name
 * @param array $option_values
 * @param mixed $option_names
 */
function be_mu_setting_select( $name, $option_values, $option_names = 'same-as-values' ) {

    // Based on the $option_names argument we could use the option values as option names
    if ( ! is_array( $option_names ) && 'same-as-values' == $option_names ) {
        $option_names = $option_values;
    }

    // Get the current value of the setting in the database
    $current_db_value = be_mu_get_setting( $name );

    // Output the select tag
    echo '<select id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" size="1">';

    // Go through all values
    for ( $i = 0; $i < count( $option_values ); $i++ ) {

        // When we see the current value in the database, we will output the selected attribute. Otherwise we just output the option tag with the value and name.
        if ( $current_db_value == $option_values[ $i ] ) {
            $selected = 'selected="selected"';
        } else {
            $selected = '';
        }
        echo '<option value="' . esc_attr( $option_values[ $i ] ) . '" ' . $selected . ' >' . esc_html( $option_names[ $i ] ) . '</option>';

    }
    echo '</select>';
}

/**
 * Display an html select form element that does not have a database value. Used for ajax calls where we do not save the selected settings.
 * @param array $option_values
 * @param mixed $option_names
 * @param array $values
 */
function be_mu_select( $name, $option_values, $option_names = 'same-as-values' ) {

    // Based on the $option_names argument we could use the option values as option names
    if ( ! is_array( $option_names ) && 'same-as-values' == $option_names ) {
        $option_names = $option_values;
    }

    echo '<select id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" size="1">';

    // Go through all values
    for ( $i = 0; $i < count( $option_values ); $i++ ) {
        echo '<option value="' . esc_attr( $option_values[ $i ] ) . '" >' . esc_html( $option_names[ $i ] ) . '</option>';
    }
    echo '</select>';
}

/**
 * Display an html checkbox form element to use in a plugin settings page. It is automatically checked based on the current setting value.
 * @param string $name
 */
function be_mu_setting_checkbox( $name ) {

    // Get the current value of the setting in the database
    $current_db_value = be_mu_get_setting( $name );

    // If it is set to on, we will display the checkbox checked
    if ( 'on' == $current_db_value ) {
        $checked = 'checked';
    } else {
        $checked = '';
    }

    // Output the checkbox
    echo '<input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '" ' . $checked . ' />';
}

/**
 * Display an html radio form element to use in a plugin settings page. It is automatically selected based on the current setting value.
 * @param string $name
 * @param array $option_values
 * @param mixed $option_names
 */
function be_mu_setting_radio( $name, $option_values, $option_names = 'same-as-values' ) {

    // Based on the $option_names argument we could use the option values as option names
    if ( ! is_array( $option_names ) && 'same-as-values' == $option_names ) {
        $option_names = $option_values;
    }

    // Get the current value of the setting in the database
    $current_db_value = be_mu_get_setting( $name );

    // Go through all values
    for ( $i = 0; $i < count( $option_values ); $i++ ) {

        // When we see the current value in the database, we will output the checked radio. Otherwise we just output the input radio tag with the value and name.
        if ( $current_db_value == $option_values[ $i ] ) {
            $checked = 'checked';
        } else {
            $checked = '';
        }
        echo '<label><input type="radio" name="' . esc_attr( $name ) . '" value="' . esc_attr( $option_values[ $i ] ) . '" ' . $checked . ' > '
            . esc_html( $option_names[ $i ] ) . '</label><br>';
    }
}

/**
 * Display an html textarea form element to use in a plugin settings page. It is automatically filled with the current setting value.
 * @param string $name
 */
function be_mu_setting_textarea( $name ) {

    // Get the current value of the setting in the database (get an empty string if it is not set)
    $current_db_value = be_mu_get_setting( $name, '' );

    // Output the textbox with the current value
    echo '<textarea id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $current_db_value ) . '</textarea>';
}

/**
 * Display a wordpress text editor to use in a plugin settings page. It is automatically filled with the current setting value.
 * Hint: Now since I implemented a fix for the height bug in WordPress 5.0, I have to remember to set the height in the script in
 * function beyondMultisiteShowSettings too if it is different than 250.
 * @param string $name
 * @param int $height
 */
function be_mu_setting_wp_editor( $name, $height = 250 ) {

    // Get the current value of the setting in the database (get an empty string if it is not set)
    $current_db_value = be_mu_get_setting( $name, '' );

    // Output the wp editor with the current value
    wp_editor(
        $current_db_value,
        esc_attr( $name ),
        array(
            'editor_height' => $height,
            'media_buttons' => false,
        )
    );
}

/**
 * Display a wordpress text editor that does not have a database value. Used for ajax calls where we do not remember the selected value.
 * Hint: Now since I implemented a fix for the height bug in WordPress 5.0, I have to remember to set the height in the script in
 * function beyondMultisiteShowSettings too if it is different than 250.
 * @param string $name
 */
function be_mu_wp_editor( $name ) {

    // Output the wp editor with an empty value
    wp_editor(
        '',
        esc_attr( $name ),
        array(
            'editor_height' => 250,
            'media_buttons' => false,
        )
    );
}

/**
 * Display an html input type text form element to use in a plugin settings page. It is automatically filled with the current setting value.
 * @param string $name
 */
function be_mu_setting_input_text( $name ) {

    // Get the current value of the setting in the database (get an empty string if it is not set)
    $current_db_value = be_mu_get_setting( $name, '' );

    // Output the input type text with the current value
    echo '<input type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $current_db_value )
        . '" onkeypress="return event.keyCode != 13;" />';
}

/**
 * Display an html input type text form element that does not have a database value. Used for ajax calls where we do not remember the selected settings.
 * @param string $name
 */
function be_mu_input_text( $name ) {
    echo '<input type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name )
        . '" value="" onkeypress="return event.keyCode != 13;" />';
}

/**
 * Display an html select form element to use in a user profile settings page. It is automatically set to the current setting value.
 * We can also choose which value to be used if the setting does not exist. This is useful because otherwise we have to create the setting for all users.
 * Now we just equate one of the values with false and create the setting for the users that need it and when it needs it.
 * @param string $name
 * @param int $user_id
 * @param string $value_if_false
 * @param array $option_values
 * @param mixed $option_names
 */
function be_mu_user_setting_select( $name, $user_id, $value_if_false, $option_values, $option_names = 'same-as-values' ) {

    // Based on the $option_names argument we could use the option values as option names
    if ( ! is_array( $option_names ) && 'same-as-values' == $option_names ) {
        $option_names = $option_values;
    }

    // Get the current value of the setting in the database
    $current_db_value = get_user_option( $name, $user_id );

    // Output the select tag
    echo '<select id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" size="1">';

    // Go through all values
    for ( $i = 0; $i < count( $option_values ); $i++ ) {

        /**
         * When we see the current value in the database, or if there is no current value and we see the one to use in such a case,
         * we will output the "selected" attribute. Otherwise we just output the option tag with the value and name.
         */
        if ( $current_db_value == $option_values[ $i ] || ( false === $current_db_value && $value_if_false === $option_values[ $i ] ) ) {
            $selected = 'selected="selected"';
        } else {
            $selected = '';
        }
        echo '<option value="' . esc_attr( $option_values[ $i ] ) . '" ' . $selected . ' >' . esc_html( $option_names[ $i ] ) . '</option>';

    }
    echo '</select>';
}

/**
 * If the blog option does not exist, it creates it, if it does exist it updates it, and retuns true on success, and false on failure
 * @param int $site_id
 * @param string $name
 * @param mixed $value
 * @return bool
 */
function be_mu_set_or_make_blog_setting( $site_id, $name, $value ) {

    // The current value of the setting (false if it does not exist)
    $current_value = get_blog_option( $site_id, $name );

    // If the setting does not exist we create it and return the status
    if ( false === $current_value ) {
        return add_blog_option( $site_id, $name, $value );

    // The current value is the same as the new one, no need to do anything so we return true
    } elseif ( $current_value == $value ) {
        return true;

    // We need to update the setting so we do and return the status
    } else {

        /**
         * Instead of just updating with update_blog_option, we will first delete the option and then add it. We do this because in some situations
         * WordPress makes many copies of the same option (might be a bug in WP core, I am not sure) and this could cause problems. Deleting the option
         * deletes all copies so we are now sure it is just one.
         */
        delete_blog_option( $site_id, $name );
        return add_blog_option( $site_id, $name, $value );
    }
}
