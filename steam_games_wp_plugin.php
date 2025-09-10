<?php
/**
 * Plugin Name: Steam游戏时长展示
 * Description: 在WordPress网站上展示Steam游戏时长和轮播效果
 * Version: 1.0
 * Author: Rubisco0326
 */

// 防止直接访问
if (!defined('ABSPATH')) {
    exit;
}

class SteamGamesDisplay {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_get_steam_games', array($this, 'get_steam_games'));
        add_action('wp_ajax_nopriv_get_steam_games', array($this, 'get_steam_games'));
        add_shortcode('steam_games', array($this, 'display_steam_games'));
        add_action('admin_menu', array($this, 'admin_menu'));
    }
    
    public function init() {
        // 插件初始化
    }
    
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('steam-games-js', plugin_dir_url(__FILE__) . 'steam-games.js', array('jquery'), '1.0', true);
        wp_enqueue_style('steam-games-css', plugin_dir_url(__FILE__) . 'steam-games.css', array(), '1.0');
        
        // 传递AJAX URL给JavaScript
        wp_localize_script('steam-games-js', 'steam_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php')
        ));
    }
    
    public function admin_menu() {
        add_options_page(
            'Steam游戏设置',
            'Steam游戏设置',
            'manage_options',
            'steam-games-settings',
            array($this, 'settings_page')
        );
    }
    
    public function settings_page() {
        if (isset($_POST['submit'])) {
            update_option('steam_api_key', sanitize_text_field($_POST['steam_api_key']));
            update_option('steam_id', sanitize_text_field($_POST['steam_id']));
            echo '<div class="notice notice-success"><p>设置已保存！</p></div>';
        }
        
        $api_key = get_option('steam_api_key', '');
        $steam_id = get_option('steam_id', '');
        ?>
        <div class="wrap">
            <h1>Steam游戏设置</h1>
            <form method="post" action="">
                <table class="form-table">
                    <tr>
                        <th scope="row">Steam API密钥</th>
                        <td>
                            <input type="text" name="steam_api_key" value="<?php echo esc_attr($api_key); ?>" class="regular-text" />
                            <p class="description">从 <a href="https://steamcommunity.com/dev/apikey" target="_blank">Steam API</a> 获取</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Steam ID</th>
                        <td>
                            <input type="text" name="steam_id" value="<?php echo esc_attr($steam_id); ?>" class="regular-text" />
                            <p class="description">您的Steam ID（数字格式）</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <h2>API测试</h2>
            <button id="test-steam-api" class="button">测试Steam API连接</button>
            <div id="api-test-result" style="margin-top: 10px;"></div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#test-steam-api').click(function() {
                    var $button = $(this);
                    var $result = $('#api-test-result');
                    
                    $button.prop('disabled', true).text('测试中...');
                    $result.html('<p style="color: #666;">正在测试Steam API连接...</p>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'get_steam_games'
                        },
                        success: function(response) {
                            if (response.success) {
                                var games = response.data;
                                var html = '<div style="padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">';
                                html += '<strong>✅ API测试成功！</strong><br>';
                                html += '找到 ' + games.length + ' 个游戏<br>';
                                if (games.length > 0) {
                                    html += '最常玩的游戏: ' + games[0].name + ' (' + Math.round(games[0].playtime_forever/60) + ' 小时)';
                                }
                                html += '</div>';
                                $result.html(html);
                            } else {
                                $result.html('<div style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;"><strong>❌ API测试失败</strong><br>错误信息: ' + response.data + '</div>');
                            }
                        },
                        error: function(xhr, status, error) {
                            $result.html('<div style="padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;"><strong>❌ 网络请求失败</strong><br>错误: ' + error + '</div>');
                        },
                        complete: function() {
                            $button.prop('disabled', false).text('测试Steam API连接');
                        }
                    });
                });
            });
            </script>
            
            <h2>使用方法</h2>
            <p>在文章或页面中使用短代码：<code>[steam_games]</code></p>
        </div>
        <?php
    }
    
    public function get_steam_games() {
        $api_key = get_option('steam_api_key');
        $steam_id = get_option('steam_id');
        
        if (empty($api_key) || empty($steam_id)) {
            wp_die('Steam API密钥或Steam ID未设置');
        }
        
        // 检查缓存
        $cache_key = 'steam_games_' . $steam_id;
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            wp_send_json_success($cached_data);
            return;
        }
        
        $url = "http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key={$api_key}&steamid={$steam_id}&format=json&include_appinfo=1";
        
        $response = wp_remote_get($url);
        
        if (is_wp_error($response)) {
            wp_send_json_error('无法获取Steam数据');
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['response']['games'])) {
            wp_send_json_error('Steam API返回数据格式错误');
            return;
        }
        
        $games = $data['response']['games'];
        
        // 按游戏时长排序
        usort($games, function($a, $b) {
            return $b['playtime_forever'] - $a['playtime_forever'];
        });
        
        // 只取前8个游戏
        $games = array_slice($games, 0, 8);
        
        // 缓存数据30分钟
        set_transient($cache_key, $games, 30 * MINUTE_IN_SECONDS);
        
        wp_send_json_success($games);
    }
    
    public function display_steam_games($atts) {
        $atts = shortcode_atts(array(
            'width' => '800px',
            'height' => '400px'
        ), $atts);
        
        ob_start();
        ?>
        <div class="steam-games-container">
            <div class="steam-header">
                <h3>我的Steam游戏库</h3>
                <p>最常玩的游戏 - 按游玩时间排序</p>
            </div>
            <div class="steam-games-grid">
                <div class="loading-state">正在加载游戏数据...</div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            loadSteamGamesFromWordPress();
        });
        </script>
        <?php
        return ob_get_clean();
    }
}

// 初始化插件
new SteamGamesDisplay();

// 插件激活时创建必要的选项
register_activation_hook(__FILE__, function() {
    add_option('steam_api_key', '');
    add_option('steam_id', '');
});

// 插件停用时清理
register_deactivation_hook(__FILE__, function() {
    delete_option('steam_api_key');
    delete_option('steam_id');
});
?>