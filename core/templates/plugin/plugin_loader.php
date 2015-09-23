<?php echo "<?php\n"; ?>

class <?php echo $name_camelized; ?>Loader extends MvcPluginLoader {

    var $db_version = '1.0';
    var $tables = array();

    function activate() {
    
        // This call needs to be made to activate this app within WP MVC
        
        $this->activate_app(__FILE__);
        
        // Perform any databases modifications related to plugin activation here, if necessary

        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        global $wpdb;
        add_option('<?php echo $name_underscored; ?>_db_version', $this->db_version);

        $basePath = dirname(__FILE__);
        $pluginPath = $basePath.DIRECTORY_SEPARATOR.'migrations';

        $sql = "
CREATE TABLE IF NOT EXISTS `plugin_migrations` (
`id` int(10) NOT NULL AUTO_INCREMENT,
`plugin` varchar(255) NOT NULL,
`version` varchar(3) NOT NULL DEFAULT '000',
PRIMARY KEY (`id`)
        );
        ";
        // Use dbDelta() to create the tables for the app here
        dbDelta($sql);

        //Search for the latest version of the plugin (will be empty if the table has just been created)
        $max = (int) $wpdb->get_var( "SELECT MAX(version) FROM plugin_migrations WHERE plugin = '<?php echo $name_underscored; ?>'" );

        //Search for migration files
        if (is_dir($pluginPath)) {
            if ($handle = opendir($pluginPath)) {
                $updates = array();
                while (false !== ($migration = readdir($handle))) {
                    if ($migration != "." && $migration != ".." ) {
                        $current = substr($migration, 0, 3);
                        if (((int) $current) > $max) {
                            $updates[$current] = file_get_contents($pluginPath.DIRECTORY_SEPARATOR.$migration);
                        }
                    }
                }
                ksort($updates);

                foreach ($updates as $v => $sql) {
                    //TODO surround with try/catch
                    // Use dbDelta() to create the tables for the app here
                    dbDelta($sql);
                    // Update last known version
                    $wpdb->insert("plugin_migrations", array(
                    "plugin" => '<?php echo $name_underscored; ?>',
                    "version" => $v
                    ));
                }
                closedir($handle);
            }
        }
        
    }

    function deactivate() {
    
        // This call needs to be made to deactivate this app within WP MVC
        
        $this->deactivate_app(__FILE__);
        
        // Perform any databases modifications related to plugin deactivation here, if necessary
    
    }

}

<?php echo '?>'; ?>