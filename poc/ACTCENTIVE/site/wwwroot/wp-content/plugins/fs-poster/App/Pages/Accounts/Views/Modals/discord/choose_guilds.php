<div>
    <label for="fs_chosen_guilds" class="fsp-is-jb">
	    <?php echo fsp__( 'Select one or more servers' ); ?>
    </label>
    <select class="fsp-form-input select2-init" id="fspChosenGuilds" name="fs_chosen_guilds[]" multiple>
        <?php
        if ( ! empty( $fsp_params[ 'new_guilds' ] ) )
        {
            foreach ( $fsp_params[ 'new_guilds' ] as $guild )
            {
                echo '<option value="' . htmlspecialchars( $guild[ 'id' ] ) . '" ' . ( ! empty( $fsp_params[ 'existing_guilds' ] ) && in_array( $guild[ 'id' ], $fsp_params[ 'existing_guilds' ] ) ? 'selected' : '' ) . '>' . htmlspecialchars( $guild[ 'name' ] ) . '</option>';
            }
        }
        ?>
    </select>
</div>