<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-20 10:54:09
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-04-03 09:37:16
*/
namespace Suyan\Lorg\Core;

class Client
{
    public $name;
    public $result = 0;
    public $severity = 0;
    public $actions = array();
    public $quantification = array();
    public $classification = array();
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public $first_seen = null;
    public $first_geoip_data = null;
    public $first_dnsbl_data = null;
    public $first_remote_host = null;
    public $multiple_remote_hosts = false;
    public $multiple_geolocations = false;
    public $last_non_autoload_visit = null;
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public $harmless_requests = 0;
    public $avg_time_delay = 0.0;
    public $std_time_delay = 0.0;
    public $index_time_delay = 0;
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public $night_time_visitor = false;

// ------------------------------------------------------------------ //

    # function: construct client and set name
    public function __construct($name)
    {
        $this->name = $name;
    }

// ------------------------------------------------------------------ //

    # function: add action to client's actions
    public function add_action(Action $action)
    {
        array_push($this->actions, $action);
    }

// ------------------------------------------------------------------ //

    # function: aggregate data used to classify client as night-/working-time visitors
    function reset_properties($Quantify, $action)
    {
        // set date of first rendezvous
        if (!isset($this->first_seen))
            $this->first_seen = $action->date;

        // set first seen remote host
        if (!isset($this->first_remote_host))
            $this->first_remote_host = $action->remote_host;

        // set first geoip-data (might change if client ident not address)
        if (!isset($this->first_geoip_data))
            $this->first_geoip_data = $action->geoip_data;

        // set first dnsbl-data (might change if client ident not address)
        if (!isset($this->first_dnsbl_entry))
            $this->first_dnsbl_data = $action->dnsbl_data;

        // set if client origins from multiple hosts
        if ($action->remote_host != $this->first_remote_host)
            $this->multiple_remote_hosts = true;

        // set if client origins from multiple geolocations
        if ($action->geoip_data[1] != $this->first_geoip_data[1])
            $this->multiple_geolocations = true;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // if we can act on the assumtion of a non-autoload request
        if ((is_string($action->path)) and (preg_match("/.*(html?|\/|" . implode('|', $Quantify->webAppExtensions) . ")$/", $action->path))){
            // get inter-request time delay
            $delay = strtotime($action->date) - strtotime($this->last_non_autoload_visit);

            // if we're within a session's timespan and don't have a future timestamp in the future
            if (($delay <= $Quantify->maxSessionDuration) and ($delay >= 0)){
                // online variance calculation of inter-request time delay
                Helper::onlineVariance($this->avg_time_delay, $this->std_time_delay, $this->index_time_delay, $delay);

                // set time of last non-autoload request
                $this->last_non_autoload_visit = $action->date; 
            }
            // first-time set last non-autoload request
            if (!isset($this->last_non_autoload_visit))
                $this->last_non_autoload_visit = $action->date;
        }
    }

// ------------------------------------------------------------------ //

    # function: classify client as creature of the night or working-time visitor
    function classify()
    {
        # final calculation for standard deviation of client's inter-request time delay
        $this->std_time_delay = sqrt(($this->index_time_delay > 1) ? $this->std_time_delay / ($this->index_time_delay - 1) : $this->std_time_delay);

        // check if geoip lookup enabled
        if (isset($this->first_geoip_data))
        {
            // extract client's geolocation
            $geolocation = explode(',', $this->first_geoip_data[1]);

            // if we've got a valid geolocation
            if ($geolocation[0] != '-')
            {
                // get date of first rendez-vous with client
                $date = strtotime($this->first_seen);
                // determine sun info in client's geolocation at that date
                $sun_info = date_sun_info($date, $geolocation[0], $geolocation[1]);
                // determine if attack happend during 'unusual' working-time, which we define as 6 hours before
                // and 10 hours after transit; note: one could also define this as the time between sunrise and
                // sunset, but it won't work out e.g. for all-sunshine hackers from the polar circle at midummer
                if (($date  < ($sun_info['transit']) - 6*3600) or ($date  > ($sun_info['transit'] + 10*3600)))
                    $this->night_time_visitor = true;
            }
        }
    }
}
?>