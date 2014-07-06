<?php
/**
 * Plugin Name: Breakfast Donate
 * Plugin URI:  https://github.com/BreakfastCraft/breakfast-donate
 * Description: Donation Tracking widget
 * Author: Bret Belgarde
 * Version 0.1.0
 * Author URI: http://bretbelgarde.com/
 **/

class BreakfastDonateWidget extends WP_Widget
{
    private $messages;
    private $donators;
    private $filename;
    private $error;

    public function __construct()
    {
        parent::WP_Widget(
            'breakfast_donate_widget',
            __('Breakfast Donate Widget', 'text_domain'),
            array('description' => __('A donation tracking widget', 'text_domain'))
        );

        $this->filename = __DIR__ . '/messages.json';
        
        if (file_exists($this->filename)) {
            
            if ($json = json_decode(file_get_contents($this->filename), true)) {
               $this->messages = $json; 
            } else {
                $this->error = 'Failed - File Read' . $this->filename;
            }
        
        } else {
            $this->error = 'Failed - File Does Not Exist' . $this->filename;
        }

    }

    public function form($instance)
    {
        ?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>"
                       type="text" value="<?php echo esc_attr( $instance['title'] ); ?>"/>
            </p>

            <p>
                <label for="<?php echo $this->get_field_id( 'donation_reward' ); ?>"><?php _e( 'Donation Reward:' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'donation_reward' ); ?>"
                       name="<?php echo $this->get_field_name( 'donation_reward' ); ?>"
                       type="text" value="<?php echo esc_attr( $instance['donation_reward'] ); ?>"/>
            </p>

             <p>
                <label for="<?php echo $this->get_field_id( 'donation_url' ); ?>"><?php _e( 'Donation URL:' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'donation_url' ); ?>"
                       name="<?php echo $this->get_field_name( 'donation_url' ); ?>"
                       type="text" value="<?php echo esc_attr( $instance['donation_url'] ); ?>"/>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'donation_max' ); ?>"><?php _e( 'Donation Amount Required:' ); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id( 'donation_max' ); ?>"
                       name="<?php echo $this->get_field_name( 'donation_max' ); ?>"
                       type="text" value="<?php echo esc_attr( $instance['donation_max'] ); ?>"/>
            </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['donation_reward'] = strip_tags($new_instance['donation_reward']);
        $instance['donation_url'] = strip_tags($new_instance['donation_url']);
        $instance['donation_max'] = strip_tags($new_instance['donation_max']);
        return $instance;

    }

    public function widget($args, $instance)
    {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $donationReward = $instance['donation_reward'];
        $donationURL = $instance['donation_url'];
        $donationMax = $instance['donation_max'];

        $donators = array();
        $currentValue = 0;
        $percentReached = 0;

        foreach ($this->messages as $message) {
            $currentValue += $message['amount'];
            
            $donation = array(
                'ign' => $message['ign'],
                'amount' => $message['amount']
            );

            array_push($donators, $donation);
        }

        $percentReached = round(($currentValue / $donationMax) * 100);

        ?>
            <section class="breakfast-donate-widget">
                <div class="row">
                    <div class="col-sm-12">
                        <!-- Progress Bar -->
                        <h3><?php echo $title ?></h3>
                        <div class="donation-goals">
                            <p>Donation Reward: <?php echo $donationReward ?></p>
                            <p class="well well-sm">
                                <span>Current: $<?php echo $currentValue ?></span> 
                                <span class="pull-right">Goal: $<?php echo $donationMax ?></span>
                        </div>
                        <div class="progress" style="height: 26px;">
                            <div class="progress-bar progress-bar-success"
                                 role="progressbar" 
                                 aria-valuenow="<?php echo $percentReached; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="<?php echo $donationMax; ?>" 
                                 style="width: <?php echo $percentReached; ?>%;">
                                 <?php echo $percentReached?>%
                             </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-12">
                        <!-- 5 Most Recemt Donors -->
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Recent Donors</h3>
                            </div>    
                            <div class="panel-body">
                                <ul class="list-group">
                                    <?php 
                                        echo (isset($this->error)) ? $this->error : '';
                                        $bailout = 5;
                                    ?>
                                    <?php foreach (array_reverse($donators) as $donator ) { ?>
                                        <li class="list-group-item">
                                            <img src="https://minotar.net/avatar/<?php echo $donator['ign'] ?>/16">&nbsp;&nbsp;<strong><?php echo $donator['ign'] ?></strong>
                                            <span class="pull-right">$ <?php echo $donator['amount'] ?></span>
                                        </li>
                                    <?php
                                            $bailout -= 1;
                                            if ($bailout <= 0) {
                                                break;
                                            }
                                        }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <!-- Donate Button -->
                    <p class="text-center"><a href="<?php echo $donationLink ?>" class="btn btn-success btn-lg btn-block">DONATE!</a></p>
                </div>
            </section>
            <hr>

        <?php
    }

}

add_action('widgets_init', function(){
     register_widget('BreakfastDonateWidget');
});
