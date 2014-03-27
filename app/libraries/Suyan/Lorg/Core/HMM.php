<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-20 12:06:17
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 18:27:35
*/
namespace Suyan\Lorg\Core;
# Algorithms ported to PHP, based on "HMM-Web: a framework for the #
# detection of attacks against Web applications" from [Corona2009] #
class HMM
{
    // number of states of the HMM
    protected $states;
    // node labels in the HMM
    protected $labels = array();
    // start probabilities as an adjazence matrix
    protected $start = array();
    // transition probabilities as an adjazence matrix
    protected $transition = array();
    // emission probabilities as an adjazence matrix
    protected $emission = array();

// ------------------------------------------------------------------ //

    # function: construct hidden markov model from a set of labels
    public function __construct($states, array $labels)
    {
        // initialize properties of HMM
        $this->states     = $states;
        $this->labels     = array_values($labels);
        $this->start      = array_fill(0, $states, 1 / $states);
        $this->transition = array_fill(0, $states, array_fill(0, $states, 1 / $states));
        $this->emission   = array_fill(0, $states, array_fill(0, count($labels), 1 / count($labels)));

    }

// ------------------------------------------------------------------ //

    # function: create random transition, emission and start probabilities
    public function randomize()
    {
        for ($i = 0; $i < $this->states; ++$i)
        {
            $this->transition[$i] = $this->get_random_array($this->states);
            $this->emission[$i]   = $this->get_random_array(count($this->labels));
        }
        $this->start = $this->get_random_array($this->states);
    }

// ------------------------------------------------------------------ //

    # function: get array of random values which sum up to 1
    protected function get_random_array($size)
    {
        $return = array();
        $left   = 1;
        for ($i = 0; $i < $size; ++$i)
        {
            if ($i === ($size - 1))
            {
                $return[] = $left;
                break;
            }
            $return[] = $v = mt_rand(0, $left * 10000) / 10000;
            $left    -= $v;
        }
        return $return;
    }

// ------------------------------------------------------------------ //

    # function: map labels to their indexes
    public function map_labels(array $labels)
    {
        $indexes = array();
        foreach ($labels as $label)
        {
            if (($key = array_search($label, $this->labels)) === false)
                throw new OutOfBoundsException();
            $indexes[] = $key;
        }
        return $indexes;
    }

// ------------------------------------------------------------------ //

    # function: get probability of validity of observation
    public function get_probability(array $observation, array $universe, $hmm_decrease)
    {
        // define observation as valid
        $probability = 1;

        // define known and unknown symbols
        $known_symbols = $unknown_symbols = array();

        // for each symbol in the observation
        foreach ($observation as $symbol)
        {
            // check if symbol is within universe
            if (in_array($symbol, $universe))
                // add to known symbols
                $known_symbols[] = $symbol;
            else
                // add to unknown symbols
                $unknown_symbols[] = $symbol;
        }

        // if observation contains known symbols
        if ($known_symbols)
            // apply logarithmic version of viterbi algorithm
            $probability = $this->test_viterbi($known_symbols);

        // if observation contains symbols never seen before
        if ($unknown_symbols)
        {
            $probability = ($probability + 5) * $hmm_decrease; // decrease probability of validity
        }

        // return double value between 1.0 (valid) and 0.0 (suspicious)
        return $probability;
    }

// ------------------------------------------------------------------ //

    # function: logarithmic version of viterbi algorithm
    public function test_viterbi(array $observation)
    {
        $len_observation = count($observation);
        $sequence = $this->map_labels($observation);
        $path = array();
        $ksi = array(array());

        # initialize base cases (t == 0)
        for ($y = 0; $y < $this->states; $y++)
        {
            $ksi[0][$y] = $this->start[$y] * $this->emission[$y][$sequence[0]];
            $path[$y] = array($y);
        }
 
        # run Viterbi for t > 0
        for ($t = 1; $t < $len_observation; $t++)
        {
            $obs_t = $sequence[$t];
            $newpath = array();
            # for all states do
            for ($y = 0; $y < $this->states; $y++)
            {
                # calculate state/probability tuple, where probablility is max
                $probability = 0.0;
                $state = 0;
                for ($y0 = 0; $y0 < $this->states; $y0++)
                {
                    $new_probability = $ksi[$t - 1][$y0] * $this->transition[$y0][$y] * $this->emission[$y][$obs_t];
                    ### echo "\nstate: $y0 | new_probability: $new_probability";
                    if ($new_probability > $probability)
                    {
                        ### echo " <<< SET!";
                        $probability = $new_probability;
                        $state = $y0;
                    }
                }
                ### echo "\n------------------------------------------------------";
                $ksi[$t][$y] = $probability;
                $newpath[$y] = $path[$state] + array($y);
            }
            # don't need to remember the old paths
            $path = $newpath;
        }

        # calculate state/probability tuple, where probablility is max
        $probability = 0.0;
        $state = 0;
        for ($y = 0; $y < $this->states; $y++)
        {
            $new_probability = ($ksi[$len_observation-1][$y]);
            ### echo "\nstate: $y0 | new_probability: $new_probability";
            if ($new_probability > $probability)
            {
                ### echo " <<< SET!";
                $probability = $new_probability;
                $state = $y;
            }
        }
        return $probability;
    }

// ------------------------------------------------------------------ //

    # function: calculate (scaled) forward variables for a given sequence
    public function forward_scaled(array $sequence, $len_sequence, &$scale)
    {
        // 1st: initialization
        for ($i = 0; $i < $this->states; ++$i)
            $scale[0] += $fwd[0][$i] = $this->start[$i] * $this->emission[$i][$sequence[0]];

        // scaling
        if ($scale[0] != 0)
            for ($i = 0; $i < $this->states; $i++)
                $fwd[0][$i] = $fwd[0][$i] / $scale[0];
        else
         // avoid divistion by zero
         $scale[0] = 1;

        // 2nd: induction
        for ($t = 1; $t < $len_sequence; $t++)
        {
            for ($i = 0; $i < $this->states; $i++)
            {
                $sum = 0.0;
                for ($j = 0; $j < $this->states; $j++)
                    $sum += $fwd[$t-1][$j] * $this->transition[$j][$i];
                $fwd[$t][$i] = $sum * $this->emission[$i][$sequence[$t]];
                $scale[$t] += $fwd[$t][$i]; // scaling coefficient
            }
            // scaling
            if ($scale[$t] != 0)
                for ($i = 0; $i < $this->states; $i++)
                    $fwd[$t][$i] = $fwd[$t][$i] / $scale[$t];
            else
            {
                $scale[$t] = 1;
            }
        }
        return $fwd;
    }
    
// ------------------------------------------------------------------ //

    # function: calculate (scaled) backward variables for a given sequence
    public function backward_scaled(array $sequence, $len_sequence, $scale)
    {
        // 1st: initialization
        for ($i = 0; $i < $this->states; $i++)
            $bwd[$len_sequence-1][$i] = 1.0 / $scale[$len_sequence-1];

        // 2nd: induction
        for ($t = $len_sequence - 2; $t >= 0; $t--)
            for ($i = 0; $i < $this->states; $i++)
            {
                $sum = 0.0;
                for ($j = 0; $j < $this->states; $j++)
                    $sum += $this->transition[$i][$j] * $this->emission[$j][$sequence[$t+1]] * $bwd[$t+1][$j];
                // no more undefined offsets
                if (!isset($bwd[$t][$i]))
                    $bwd[$t][$i] = 0.0;
                $bwd[$t][$i] += $sum / $scale[$t];
            }
        return $bwd;
    }

// ------------------------------------------------------------------ //

    # function: modified version of Baum-Welch algorithm to learn probabilities
    # on multiple observations sequences as described by Rabiner and Juang ('89)
    public function train_baum_welch(array $observations, array $universe, $max_iter, $tolerance)
    {
        // print some information

        // set number of observations and symbols in universe
        $num_observations = count($observations);
        $num_universe = count($universe);

        // randomize observations (and remove their key)
        shuffle($observations);

        // calculate initial model log-likelihood
        $likelihood = 0; $old_likelihood = -1;

        // first, map labels and count sequences for all observations
        for ($i = 0; $i < $num_observations; $i++)
        {
            $sequences[$i] = $this->map_labels($observations[$i]);
            $len_sequences[$i] = count($sequences[$i]);
        }

        // loop until convergence or max iterations is reached
        for ($iter=0; $iter < $max_iter; $iter++)
        {
            // for each sequence in the observations input
            for ($cur_observation = 0; $cur_observation < $num_observations; $cur_observation++)
            {
                // current sequence
                $sequence = $sequences[$cur_observation];
                $len_sequence = $len_sequences[$cur_observation];

                // scale factors
                $scale = array_fill(0, $len_sequence, 0.0);

                // calculate forward and backward variables
                $fwd = $this->forward_scaled($sequence, $len_sequence, $scale);
                $bwd = $this->backward_scaled($sequence, $len_sequence, $scale);

                ### echo "-------------------------------------------------------------------------------------------------------------------------\n";
                // calculate gamma values for next computations
                for ($t = 0; $t < $len_sequence; $t++)
                {
                    $s = 0;
                    for ($cur_state = 0; $cur_state < $this->states; $cur_state++)
                        $s += $gamma[$cur_observation][$t][$cur_state] = $fwd[$t][$cur_state] * $bwd[$t][$cur_state];
                    if ($s != 0) // scaling
                        for ($cur_state = 0; $cur_state < $this->states; $cur_state++)
                        {
                            ### $old = $gamma[$cur_observation][$t][$cur_state];
                            $gamma[$cur_observation][$t][$cur_state] /= $s;
                            ### echo "[calculation gamma]\tcur_observation = $cur_observation\tt = $t\t$cur_state = $cur_state\t\t| value = " . $gamma[$cur_observation][$t][$cur_state] . "\t| old = $old\n";
                        }
                }

                ### echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\n";
                // calculate ksi values for next computations
                for ($t = 0; $t < $len_sequence - 1; $t++)
                {
                    $s = 0;
                    for ($cur_state = 0; $cur_state < $this->states; $cur_state++)
                        for ($l = 0; $l < $this->states; $l++)
                            $s += $ksi[$cur_observation][$t][$cur_state][$l] = $fwd[$t][$cur_state] * $this->transition[$cur_state][$l] * $bwd[$t + 1][$l] * $this->emission[$l][$sequence[$t + 1]];
                    if ($s != 0) // scaling
                    {
                        for ($cur_state = 0; $cur_state < $this->states; $cur_state++)
                            for ($l = 0; $l < $this->states; $l++)
                            {
                                ### $old = $ksi[$cur_observation][$t][$cur_state][$l];
                                $ksi[$cur_observation][$t][$cur_state][$l] /= $s;
                                ### echo "[calculation ksi]\tcur_observation = $cur_observation\tt = $t\tcur_state = $cur_state\tl = $l\t| value = " . $ksi[$cur_observation][$t][$cur_state][$l] . "\t| old = $old\n";
                            }
                    }
                }

                // compute log-likelihood for the given sequence
                for ($t = 0; $t < $len_sequence; $t++)
                    $likelihood += log($scale[$t]);
            }

            // average likelihood for all sequences
            $likelihood /= $num_observations;

            // check if the model has converged or we should stop
            if ($this->check_convergence($old_likelihood, $likelihood, $iter, $max_iter, $tolerance))
            {
                return $likelihood;
            }

            // continue with parameter re-estimation
            $old_likelihood = $likelihood; $likelihood = 0.0;

            for ($cur_state = 0; $cur_state < $this->states; $cur_state++)
            {
                ### echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\n";

                // re-estimation of initial state probabilities
                $sum_start = 0.0;
                for ($cur_observation = 0; $cur_observation < $num_observations; $cur_observation++)
                    $sum_start += $gamma[$cur_observation][0][$cur_state];
                ### $old = $this->start[$cur_state];
                $this->start[$cur_state] = $sum_start / $num_observations;
                ### echo "[re-estimation pi]\tcur_state = $cur_state\t\t\t\t| value = $sum_start \t| old = $old\n";
                ### echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\n";

                // re-estimation of transition probabilities 
                for ($j = 0; $j < $this->states; $j++)
                {
                    $den_transition = 0.0;
                    $num_transition = 0.0;

                    for ($cur_observation = 0; $cur_observation < $num_observations; $cur_observation++)
                    {
                        $len_sequence = $len_sequences[$cur_observation];
                        for ($l = 0; $l < $len_sequence - 1; $l++)
                        {
                            $num_transition += $ksi[$cur_observation][$l][$cur_state][$j];
                            $den_transition += $gamma[$cur_observation][$l][$cur_state];
                        }
                    }

                    ### $old = $this->transition[$cur_state][$j];
                    $this->transition[$cur_state][$j] = ($den_transition != 0) ? $num_transition / $den_transition : 0.0;
                    ### echo "[re-estimation A]\tcur_state = $cur_state\tj = $j\t\t\t| value = " . $num_transition / $den_transition . " \t| old = $old\n";
                }
                ### echo "- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -\n";

                // re-estimation of emission probabilities
                for ($j = 0; $j < $num_universe; $j++)
                {
                    $den_emission = 0.0;
                    $num_emission = 0.0;

                    for ($cur_observation = 0; $cur_observation < $num_observations; $cur_observation++)
                    {
                        $len_sequence = $len_sequences[$cur_observation];
                        $sequence = $sequences[$cur_observation];
                        for ($l = 0; $l < $len_sequence; $l++)
                        {
                            if ($sequence[$l] == $j)
                                $num_emission += $gamma[$cur_observation][$l][$cur_state];
                            $den_emission += $gamma[$cur_observation][$l][$cur_state];
                        }
                    }

                    ### $old = $this->emission[$cur_state][$j];
                    // avoid locking a parameter in zero
                    $this->emission[$cur_state][$j] = ($num_emission == 0) ? 1e-10 : $num_emission / $den_emission;
                    ### echo "[re-estimation B]\tcur_state = $cur_state\tj = $j\t\t\t| value = " . ($num_emission / $den_emission) . " \t| old = $old\n";
                }
            }
        }
    }

// ------------------------------------------------------------------ //

    # function: check if a model has converged given the likelihoods between
    # two iterations of the Baum-Welch algorithm and criteria for convergence
    function check_convergence($old_likelihood, $likelihood, $iter, $max_iter, $tolerance)
    {

        // Update and verify stop criteria
        if ($tolerance > 0)
        {
            // Stopping criteria is likelihood convergence
            if (abs($old_likelihood - $likelihood) <= $tolerance)
            {
                return true;
            }
            if ($max_iter > 0)
                // Maximum iterations should also be respected
                if ($iter >= $max_iter)
                {
                    return true;
                }
        }
        else
            // stopping criteria is number of iterations
            if ($iter == $max_iter)
            {
                return true;
            }
        // check if we have reached an invalid state1
        if (is_nan($likelihood) || is_infinite($likelihood))
        {
            return true;
        }
        return false;
    }
}
?>