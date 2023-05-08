<?php
/**Plugin Name: Simple Contact Form 
 * Plugin URI: https://www.your-site.com/
 * Description: Simple Contact Form YT Tutorial
 * Author: Haw Zong Han
 * Author URI: https://www.your-site.com/
 * Version: 1.0.1
 * Text Domain: simple-contact-form
 */


 
if( !defined('ABSPATH'))
{
    echo 'Stop trying to force browse';
    exit;
}

class SimpleContactForm {

    public function __construct()
    {
        //Create custom post type
        add_action('init', array($this, 'create_custom_post_type'));
        //$this refers to the SimpleContactForm class. Plugins run this contruct method when it is instantiated. The init hook runs after Wordpress environment is loaded. This line means when init hook is run, you run 'create_custom_post_type

        //Add assets (js, css, etc)
        add_action('wp_enqueue_scripts', array($this, 'load_assets'));
        
        //Add shortcode
        add_shortcode('contact-form', array($this, 'load_shortcode'));

        //Load Javascript
        add_action('wp_footer', array($this, 'load_scripts'));

        //Register REST API
        add_action('rest_api_init', array($this, 'register_rest_api'));

        // //
        // add_action('rest_api_init', array($this, 'handle_contact_form'));
        

    }

    public function create_custom_post_type()
    {
        // echo "<script>alert('IT LOADED')</script>"

        //Creating the options:
        $args = array(
            'public' => true,
            'has_archive' => true, //archive of contact forms that have been filled out
            'supports' => array('title'), //supports only 'title', can add more
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability' => 'manage_options', //the capability of the administrator
            'labels' => array(
                'name' => 'Contact Form',
                'singuar_name' => 'Contact Form Entry'
            ),
            'menue_icon' => 'dashicons-media-text',
        );

        //To load the custom post type
        register_post_type('simple_contact_form', $args);  
        

    }

    public function load_assets()
    {
        wp_enqueue_style(
            'simple_contact-form',
            plugin_dir_url( __FILE__) . '/css/simple-contact-form.css',
            array(),
            1,
            'all'
        ); //added stylesheet file

        wp_enqueue_script(
            'simple_contact-form', 
            plugin_dir_url( __FILE__) . '/js/simple-contact-form.js',
            array('jquery'),
            1,
            true

        );
    }

    public function load_shortcode()
    {?>
    <div class="simple-contact-form">
        <h1>Send us an email</h1>
        <p>Please fill the below form</p>

        <form id="simple-contact-form__form">

            <div class="form-group mb-2">
                <input name="name" type="text" placeholder="Name">
            </div>

            <div class="form-group mb-2">
                <input name="email" type="email" placeholder="Email">
            </div>

            <div class="form-group mb-2">
                <input name="phone" type="tel" placeholder="Phone">
            </div>

            <div class="form-group mb-2">
                <textarea name="message" placeholder="Type your message"></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-success btn-block w-100">Send Message</button>
            </div>
        </form>
    </div>
    <?php }


    public function load_scripts()
    {?>
        <script>

            var nonce = '<?php echo wp_create_nonce('wp_rest');?>'; //this creates a number to be passed on in the headers at ajax

            (function($){

                ('#simple-contact-form__form').submit( function(event){
                    event.preventDefault();

                    var form = $(this).serialize();

                    console.log(form)

                    $.ajax({
                        method:'post',
                        url: '<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email');?>' // null for block id and simple-contact... for endpoint. Endpoint is just a joining of the register_ret_api function
                        headers: { 'X-WP-Nonce': nonce },
                        data: form
                    })

                    alert("Submitted")
                });

            })(jQuery)
        </script>
    <?php }

    
    public function register_rest_api(){

        register_rest_route('simple-contact-form/v1', 'send-email', array(
            'methods' => 'POST',
            'callback' => array ($this, 'handle_conact_form')
        ));

    }

    public function handle_contact_form($data){
        $headers = $data -> get_headers();
        $params = $data -> get_params();
        $nonce = 123321321;


        if (!wp_verify_nonce($nonce, 'wp_rest'))
        {
            return new WP_REST_Response('Message not sent', 422);
        }

        // $post_id = wp_insert_post([
        //     'post_type' => "simple_contact_form", //our custom post type
        //     'post_title' => 'Contact enquiry',
        //     'post_status' => 'publish'

        // ]);

        // if($post_id){
        //     return new WP_REST_Response('Thank you for your email', 200);
        // }

    }



}

new SimpleContactForm;