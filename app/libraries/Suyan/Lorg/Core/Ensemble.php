<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-21 08:52:37
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 18:27:42
*/
namespace Suyan\Lorg\Core;

# Algorithms ported to PHP, based on "HMM-Web: a framework for the #
# detection of attacks against Web applications" from [Corona2009] #
class Ensemble
{
    // models within this ensemble
    protected $models = array();

    # function: construct ensemble of HMMs from a set of labels
    public function __construct(array $labels, $all_values, $path, $parameter, $hmm_num_models)
    {
        $sum_len = 0;
        foreach($all_values as $value)
        {
            $char_lst = array();
            foreach ($value as $char)
            {
                if (!in_array ($char, $char_lst))
                {
                    $char_lst[] = $char;
                }
            }
            $sum_len += count($char_lst);
        }
        $states = round($sum_len / count($all_values));

        if ($states < 1)
        {
            print_message(1, "[#] Number of states is zero, setting to one\n");
            $states = 1;
        }

        # create HMMs of ensemble
        for ($i=0; $i<$hmm_num_models; $i++)
        {
            // create an HMM with n states
            $hmm = new HMM($states, $labels);
            $hmm->randomize();
            $this->models[] = $hmm;

            ### echo "\n======================= <DEBUG: HMM ensemble =======================\n";
            ### print_r($this);
            ### echo "======================= </DEBUG: HMM ensemble> ======================\n";
        }
    }

// ------------------------------------------------------------------ //

    # function: train ensemble of HMMs
    public function train($observations, $max_iter, $universe, $tolerance)
    {
        // for all HMMs in ensemble do
        foreach($this->models as $hmm)
            // make HMM learn using the Baum-Welch algorithm
                        $hmm->train_baum_welch($observations, $universe, $max_iter, $tolerance);
    }

// ------------------------------------------------------------------ //

    # function: test ensemble of HMMs
    public function test($observation, $universe, $hmm_decrease)
    {
        // for all HMMs in ensemble do
        foreach($this->models as $hmm)
            // get probability of validity of observation
            $probabilities[] = $hmm->get_probability($observation, $universe, $hmm_decrease);

        // use maximum rule for MCS classification
        return max($probabilities);
    }    
}
?>