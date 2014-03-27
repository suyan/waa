<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-03-24 11:00:52
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-24 12:24:03
*/
namespace Suyan\Lorg\Core;

class Mcshmm
{
    // hmm
    public $hmmMinLearn = 50;
    public $hmmMaxLearn = 150;
    public $hmmMaxIter = 100;
    public $hmmTolerance = 1.0E-5;
    public $hmmDecrease = 1.0E-10;
    public $hmmNumModels = 5;
    public $listOfEnsembles;

    // vector
    public $addVector = array('path', 'argnames', 'cookie', 'agent', 'all');
    public $vectorCount = 0;

    public $log;

    public function __construct($opts, $log){
        if(!isset($log)) return false;
        $this->log = $log;

        foreach($opts as $key => $value){
            $this->$key = $value;
        }
    }

    # function: add information needed for mcshmm detection to dataset
    function aggregateMcshmm($path, $request, $client, &$dataset){
        $add_vector = $this->addVector;
        $add_vector[] = 'query';
        foreach ($add_vector as $vector){
            switch ($vector){
                case 'query':
                    $vectors = &$dataset['query'][$path]['parameters'];
                    break;
                case 'argnames':
                    $vectors = &$dataset['query'][$path]['argnames'];
                    break;
                default:
                    $vectors = &$dataset[$vector];
            }

            if (!isset($request[$vector]))
                continue;

            if (($vector == 'query') and is_array($request['query'])){
                foreach($request['query'] as $parameter => $value){
                    $parameters = &$vectors[$parameter];

                    if (isset($parameters[$client]))
                        continue;

                    if (!isset($parameters) or (count($parameters) < $this->hmmMaxLearn)){
                        $value_subst = Helper::convertAlphanumeric($value);
                        $parameters[$client] = str_split($value_subst);
                        $this->vectorCount++;
                    }
                }
            }

            if (isset($vectors[$client]))
                continue;

            if ($vector == 'argnames' and is_array($request['argnames'])){
                foreach ($request[$vector] as $argname){
                    if (!isset($vectors) or (count($vectors) < $this->hmmMaxLearn)){
                        $vectors[$client] = $argname;
                        $this->vectorCount++;
                    }
                }
            }

            if (($vector == 'cookie') or ($vector == 'agent') or ($vector == 'path')){
                if (!isset($vectors) or (count($vectors) < $this->hmmMaxLearn)){
                    $vectors[$client] = str_split(Helper::convertAlphanumeric($request[$vector]));
                    $this->vectorCount++;
                }
            }
        }
    }

    # function: train dataset for anomaly detection with hidden markov models
    function trainingMcshmm($dataset){
        $this->log->log('- 训练mcshmm');
        # set counter for process bar
        $observations = 0;

        if (!isset($dataset['query']))
            return null;

        $add_vector = $this->addVector;
        $add_vector[] = 'query';
        foreach ($add_vector as $vector){
            if ($vector == 'query'){
                
                foreach($dataset['query'] as $path => $query){
                    if (!isset($query['parameters']))
                        continue;

                    foreach($query['parameters'] as $parameter => $training_set){

                        $training_set_count = count($training_set);

                        if ($training_set_count >= $this->hmmMinLearn){
                            $universe = array_unique(call_User_Func_Array('array_merge', $training_set));

                            $hmm_ensemble = new Ensemble($universe, $training_set, $path, $parameter, $this->hmmNumModels);
                            $hmm_ensemble->train($training_set, $this->hmmMaxIter, $universe, $this->hmmTolerance);
                            $this->listOfEnsembles['query'][$path][$parameter] = $hmm_ensemble;
                        }else{
                            $observations = ($training_set_count > $observations) ? $training_set_count : $observations;
                        }
                    }
                }
                
                if ($observations < $this->hmmMinLearn){
                    $this->log->log('  - hmm无法执行，数据不够');
                }
            }
        }
    }

    # function: anomaly detection using hidden markov models (testing phase)
    function detectionMcshmm($path, $request){
        if (isset($request['query']) and is_array($request['query'])){
            foreach($request['query'] as $parameter => $value){
                $value_subst = Helper::convertAlphanumeric($value);
                $test_set = str_split($value_subst);
                if (isset($this->listOfEnsembles['query'][$path][$parameter])){
                    $universe = array_unique(call_User_Func_Array('array_merge', $this->dataset['query'][$path]['parameters'][$parameter]));
                    $result_mcshmm[] = $this->listOfEnsembles['query'][$path][$parameter]->test($test_set, $universe, $hmm_decrease);
                }
            }

            if (isset($result_mcshmm)){
                if (min($result_mcshmm) == 0)
                    $result_mcshmm = 100;
                else
                    $result_mcshmm = round(log(1 / min($result_mcshmm), 40));

                if ($result_mcshmm < 0)
                    $result_mcshmm = 0;

                return($result_mcshmm);
            }
        }
    }
}