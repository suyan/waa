<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-20 12:04:52
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-23 20:26:29
*/
namespace Suyan\Lorg\Core;

class Session
{
    public $classification = array();
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    #!# public $requests_made = array();
    #!# public $dirnames_visited = array();
    public $last_non_autoload_visit = null;
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public $number_of_requests = 0;
    #!# public $rel_number_of_requests = 0.0;
    ### public $avg_requests_per_path = 0.0;
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public $number_of_webapp_requests = 0;
    #!# public $ratio_of_webapp_requests = 0.0;
    #!# public $ratio_of_image_requests = 0.0;
    #!# public $ratio_of_repeated_requests = 0.0;
    public $requests_robots_file = false;
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public $avg_time_delay = null;
    public $std_time_delay = null;
    public $index_time_delay = 0;
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public $ratio_of_GET_requests = null;
    public $ratio_of_HEAD_requests = null;
    public $ratio_of_POST_requests = null;
    public $ratio_of_other_requests = null;
    public $ratio_of_non_rfc2616_requests = null;
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    public $ratio_of_status_code_4xx = 0.0;

    // ---------------------------------------------------------------- //

    # function: aggregate data used to classify session as spawned by human or machine
    function reset_properties($Detect, $action){
        // determine method
        $method = explode(' ', $action->data['Request']);
        $method = in_array($method[0], $Detect->allowedHttpMethods) ? $method[0] : 'non_rfc2616';

        // determine status
        $status = isset($action->data['Final-Status']) ? $action->data['Final-Status'] : null;

        // determine referer
        $referer = isset($action->data['Referer']) ? $action->data['Referer'] : null;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // increment number of requests in this session
        $this->number_of_requests++;

        // check if robots.txt file requested
        if ($action->path == '/robots.txt')
            $this->requests_robots_file = true;

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // if we can act on the assumtion of a non-autoload request
        if ((is_string($action->path)) and (preg_match("/.*(html?|\/|" . implode('|', $Detect->webAppExtensions) . ")$/", $action->path))){
            $this->number_of_webapp_requests++;
            if (isset($this->last_non_autoload_visit) and isset($action->date))
            {
                // get inter-request time delay
                $delay = strtotime($action->date) - strtotime($this->last_non_autoload_visit);

                // if we're within a valid session's timespan
                if ($delay <= $Detect->maxSessionDuration)
                {
                    // online variance calculation of inter-request time delay
                    Helper::onlineVariance($this->avg_time_delay, $this->std_time_delay, $this->index_time_delay, $delay);;
                }
            }
            $this->last_non_autoload_visit = $action->date;
        }

        if (in_array($method, array('GET', 'POST', 'HEAD', 'non_rfc2616'))){
            $var = 'ratio_of_'.$method.'_requests';
            $this->$var++;
        }else
            $this->ratio_of_other_requests++;

        // increase number of 4xx response codes
        if (preg_match("/^(4)[0-9]+$/", $status))
            $this->ratio_of_status_code_4xx++;
    }

    // ---------------------------------------------------------------- //

    # function: classify session as spawned by human or machine (robot, scanner)
    function classify($request_count)
    {
        $this->ratio_of_status_code_4xx /= $this->number_of_requests;

        // set client's ratio of GET/HEAD/POST/non-rfc2616/other requests
        $this->ratio_of_GET_requests /= $this->number_of_requests;
        $this->ratio_of_HEAD_requests /= $this->number_of_requests;
        $this->ratio_of_POST_requests /= $this->number_of_requests;
        $this->ratio_of_non_rfc2616_requests /= $this->number_of_requests;
        $this->ratio_of_other_requests /= $this->number_of_requests;

        # final calculation for standard deviation of a session's inter-request time delay
        $this->std_time_delay = sqrt(($this->index_time_delay > 1) ? $this->std_time_delay / ($this->index_time_delay - 1) : $this->std_time_delay);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        // human attacker by default
        $classification = 'human attacker'; # targeted probing

        // static or fuzzy scan
        if ($this->requests_robots_file
        or ($this->avg_time_delay < 1 and !is_null($this->avg_time_delay))
        or ($this->number_of_webapp_requests > 1000)
        or ($this->ratio_of_status_code_4xx > sqrt($this->number_of_requests) / $this->number_of_requests)
        or ($this->ratio_of_HEAD_requests + $this->ratio_of_non_rfc2616_requests + $this->ratio_of_other_requests > 0))
            $classification = 'targeted scan';

        // random scan for specific vulnerability, if client has made only one request
        if (($this->number_of_requests == 1))
            $classification = 'random scan';

        return $classification;
    }
}
?>