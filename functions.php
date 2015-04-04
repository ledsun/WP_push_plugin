<?php
/*
Plugin Name: ServiceWorkerPlugin
Plugin URI: http://pistatium.appspot.com
Description: serviceworker
Version: 1.0
Author: kimihiro_n
Author URI: http://pistatium.appspot.com
License:
*/

class SW_ButtonWidget extends WP_Widget {

     /** constructor -- name this the same as the class above */
    function SW_ButtonWidget() {
        parent::WP_Widget(false, $name = 'Push通知ボタン');
    }
    /** @see WP_Widget::widget -- do not rename this */
    function widget($args, $instance) {
        extract( $args );
        $title      = apply_filters('widget_title', $instance['title']);
        $message    = $instance['message'];
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
                            <div style="border:1px solid #aaa; height:200px">
                                <input type="button" class="subscribe_button" name="name" value="通知を受け取ります" disabled>
                                <span class="subscribe_status"></span>
                                <script>
                                    window.addEventListener('load', function() {
                                        navigator.serviceWorker.register('/sw/service-worker.js')
                                            .then(onRegiseter)
                                            .catch(function(e) {
                                                console.error(e);
                                            });
                                    });

                                    function onRegiseter(sw) {
                                        enableButton();
                                    }

                                    function enableButton() {
                                        var button = document.querySelector('.subscribe_button');

                                        button.addEventListener('click', function() {
                                            navigator.serviceWorker.ready
                                                .then(subscribe)
                                                .then(function(sub) {
                                                    console.log(sub.subscriptionId);
                                                    fetch('./service_worker.php?token=' + sub.subscriptionId)
                                                        .then(function(){
                                                            document.querySelector('.subscribe_status').innerHTML = '購読完了';
                                                        })
                                                        .catch(function(e) {
                                                            console.error(e);
                                                        });

                                                })
                                                .catch(function(e) {
                                                    console.error(e);
                                                });
                                        });
                                        button.disabled = false;
                                    }

                                    function subscribe(sw) {
                                        return sw.pushManager.subscribe();
                                    }
                                </script>
                            </div>
              <?php echo $after_widget; ?>
        <?php
    }

    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['message'] = strip_tags($new_instance['message']);
        return $instance;
    }

    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {

        $title      = esc_attr($instance['title']);
        $message    = esc_attr($instance['message']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('message'); ?>"><?php _e('Simple Message'); ?></label>
          <input class="widefat" id="<?php echo $this->get_field_id('message'); ?>" name="<?php echo $this->get_field_name('message'); ?>" type="text" value="<?php echo $message; ?>" />
        </p>
        <?php
    }



}

function cmt_activate($tablename) {
    global $wpdb;
    //DBのバージョン
    $cmt_db_version = '1.3';
    //現在のDBバージョン取得
    $installed_ver = get_option( 'cmt_meta_version' );
    // DBバージョンが違ったら作成
    if( $installed_ver != $cmt_db_version ) {
        $sql = "CREATE TABLE " . $tablename . " (
              token varchar(255) NOT NULL PRIMARY KEY,
              token_long text NOT NULL,
            )
            CHARACTER SET 'utf8';";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        //オプションにDBバージョン保存
        update_option('cmt_meta_version', $cmt_db_version);
    }
}

add_action('widgets_init', create_function('', 'return register_widget("SW_ButtonWidget");'));
global $wpdb;
// 接頭辞（wp_）を付けてテーブル名を設定
$tablename = "wp_tokens";
cmt_activate($tablename);
function send_gcm() {
    global $wpdb;
    $myrows = $wpdb->get_results( "SELECT token_long FROM wp_tokens" );
    $registrations = "";
    foreach ($myrows as $row) {
        var_dump($row);
        $registrations .= '\\"' . $row->token_long . '\\",';
    }

    $cmd = 'curl --header "Authorization: key=AIzaSyA-uX-p3-PVjN_Te8OvJ55QfvHhGQH_-mE" --header Content-Type:"application/json" https://android.googleapis.com/gcm/send -d "{\"registration_ids\":[' . $registrations . '\"dummy\"]}"';
    exec($cmd);
}
function push_user($post_id) {
    send_gcm();
};


add_action("post_updated", "push_user");
