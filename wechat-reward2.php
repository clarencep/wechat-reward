<?php
/**
 * @package Wechat Reward2(微信打赏)
 * @version 1.7
 */
/*
Plugin Name: 微信打赏
Plugin URI: https://github.com/clarencep/wp-wechat-reward2
Description: 在文章末尾添加微信打赏功能，如果读者觉得这篇文章对他有用，可以用微信扫一扫打赏赞助。
Author: clarencep
Version: 2.0
Author URI: http://www.clarencep.com
*/

define('WR2PATH',dirname(__FILE__));

class WechatReward2
{
    public function __Construct()
    {
        add_filter('the_content', array($this,'add_pay'));
        add_action('admin_menu', array($this,'WR_add_pages'));
        add_filter('plugin_action_links', array($this,'wechat_reward_plugin_setting'), 10, 2);
    }

    /**
     * 加载js和css
     */
    public function load()
    {
        //在jqeury之后加载js文件
        //wp_register_script('wechat-reward', plugins_url( '/assets/wechat-reward.js', __FILE__ ), array('jquery'));
        //wp_enqueue_script('wechat-reward');

        wp_register_style('wechat-reward', plugins_url( '/assets/wechat-reward.css', __FILE__ ));

        //确保在底部加载css样式，覆盖主题的样式
        add_action('wp_footer',array($this,'add_css'));
    }

    public function add_css()
    {
        wp_enqueue_style( 'wechat-reward');
    }

    //在文章末尾添加打赏图标
    public function add_pay($content)
    {
        $QRpic = get_option('wechat-reward-QR-pic');
        $QRpic = $QRpic ? $QRpic : plugins_url( '/assets/wxpay.png', __FILE__ );
        $QRdesc = get_option("wechat-reward-QR-desc");
        $QRtip = get_option("wechat-reward-QR-tip");
        $wxIcon = plugins_url( '/assets/ico-wechat.jpg', __FILE__ );
        $pay = <<<PAY_HTML
        <div class="gave" title="$QRtip">
            <a href="javascript:;" >打赏</a>
            <div class="code" style="display: none">
                <img src="$QRpic" alt="微信扫一扫打赏">
                <div><img src="$wxIcon" alt="微信logo" class="ico-wechat">$QRdesc</i></div>
            </div>
        </div>
PAY_HTML;

        //本插件只在文章页和非手机访问有效
        if(is_single() && !wp_is_mobile()){
            $this->load();
            $content .= $pay;
        }
        return $content;
    }

    //设置link
    public function wechat_reward_plugin_setting( $links, $file )
    {
        if($file == 'wechat-reward/wechat-reward.php'){
            $settings_link = '<a href="' . admin_url( 'options-general.php?page=upload_wechat_QR' ) . '">' . __('Settings') . '</a>';
            array_unshift( $links, $settings_link ); // before other links
        }
        return $links;
    }

    //微信打赏设置菜单
    function WR_add_pages() {
        add_options_page( '微信打赏', '微信打赏', 'manage_options', 'upload_wechat_QR', array($this,'upload_wechat_QR'));
    }

    //管理页面
    public function upload_wechat_QR()
    {
        if(isset($_POST['submit']) && $_SERVER['REQUEST_METHOD']=='POST'){
            update_option('wechat-reward-QR-pic',$_POST['wechatQR'] ?: '');
            update_option('wechat-reward-QR-desc',$_POST['wechatQRDesc'] ?: '');
            update_option('wechat-reward-QR-tip',$_POST['wechatQRTip'] ?: '');
            $this->upload_success();
        }

        $QRpic = get_option('wechat-reward-QR-pic');
        $QRdesc = get_option("wechat-reward-QR-desc") ?: '好文要赏！';
        $QRtip = get_option("wechat-reward-QR-tip") ?: '您的支持是我最大的动力，欢迎打开微信扫一扫来打赏~';
?>
    <div class="wrap">
        <h2>设置微信打赏二维码</h2>
        <p>
            请先通过手机微信获取付款二维码，操作步骤：
        </p>
        <p>
            1.打开微信，点击右上角“+”号，点击“收钱”，即可进入微信收钱页面<br>
            2.长按二维码，点击“保存图片”，即可保存图片到手机<br>
            3.将图片上传到电脑，可以通过微信传输助手传到电脑，或者其他方式将图片传到电脑<br>
            4.将二维码图片传到WordPress站点，在WordPress后台“多媒体”-"添加"，上传二维码，<span style="color: red; ">然后复制上传到服务器的二维码图片的url</span><br>
            提示：建议把微信生成的二维码图片先进行裁剪再上传。有任何疑问请发邮件到clarence@clarencep.com，将第一时间回复，谢谢！
        </p>
        <form action="<?= admin_url( 'options-general.php?page=upload_wechat_QR' ) ?>" name="settings-WR" method="post">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th><label for="QR">微信支付二维码URL</label></th>
                        <td><input type="text" class="regular-text code" value="<?= $QRpic ?>" id="QR" name="wechatQR"></td>
                    </tr>
                    <tr>
                        <th><label for="QRDesc">打赏说明</label></th>
                        <td><input type="text" class="regular-text code" value="<?= $QRdesc ?>" id="QRDesc" name="wechatQRDesc"></td>
                    </tr>
                    <tr>
                        <th><label for="QR">打赏小提示(tooltip)</label></th>
                        <td><input type="text" class="regular-text code" value="<?= $QRtip ?>" id="QRTip" name="wechatQRTip"></td>
                    </tr>
                </tbody>
            </table>
            <p class="submit"><input type="submit" value="保存更改" class="button button-primary" id="submit" name="submit"></p>
        </form>
        <hr>
        <p>如果你觉得这个插件不错，给我打赏吧！！微信扫一扫:</p>
        <p><img src="<?= plugins_url( '/assets/wxpay.png', __FILE__ ) ?>" alt="微信打赏二维码"> </p>
    </div>
<?php
    }

    //保存成功提示
    public function upload_success()
    {
        echo '<div class="updated "><p>更新成功！打开一篇文章页看看效果吧~~</p></div>';
    }
}

new WechatReward2();

//微信打赏挂件
class WR2_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'WR2WIDGET', // Base ID
            '微信打赏挂件', // Name
            array( 'description' => '给博客增加微信打赏挂件' )
        );
    }

    //前台显示
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }
        $QRpic = get_option('wechat-reward-QR-pic');
        ?>
        <div class="wrqr">
            <img src="<?= $QRpic ?>" alt="微信打赏二维码"><br>
            <span>微信扫一扫</span>
        </div>
        <?php
        echo $args['after_widget'];
    }

    //后台小工具设置
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
    }

    //更新设置
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

        return $instance;
    }
}

//注册微信打赏挂件
function register_WR2_widget() {
    register_widget( 'WR2_Widget' );
}

add_action( 'widgets_init', 'register_WR2_widget' );

