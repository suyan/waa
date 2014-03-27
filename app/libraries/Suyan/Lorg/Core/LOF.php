<?php
/* 
* @Author: Su Yan <http://yansu.org>
* @Date:   2014-01-20 12:07:03
* @Last Modified by:   Su Yan
* @Last Modified time: 2014-03-22 18:27:58
*/
namespace Suyan\Lorg\Core;

class LOF
{
    // number of elements
    protected $N;
    // number of nearest neighbors
    protected $k;
    // data to be inspected
    protected $data = array();
    // cache for log, lrd and distances
    protected $cache = array();
    // database of nearest neighbors
    protected $nearest_neighbor = array();
    // defines if we deal with values or arrays
    protected $is_values = true;

// ------------------------------------------------------------------ //

    # function: construct instance of local outlier factor from data
    public function __construct(array $lof_data, $lof_neighbors, &$lof_cache)
    {
        // initialize properties of LOF
        $this->data  = $lof_data;
        $this->cache = $lof_cache;
        $this->k     = $lof_neighbors;
        $this->N     = count($lof_data);

        // switch between value and array distance calculaten
        if (is_array(end($this->data)))
            $this->is_values = false;

    }

// ------------------------------------------------------------------ //

    # function: return local outlier factor for item
    public function run($item, $value)
    {
        // add current item to dataset
        $this->data[$item] = $value;

        // find k nearest neighbors of item
        $this->set_nearest_neighbors($item);

        // calculate local outlier factor (lof)
        $lof = $this->local_outlier_factor($item);

        return $lof;
    }

// ------------------------------------------------------------------ //

    # function: find k nearest neighbors of A
    protected function set_nearest_neighbors($A)
    {
        foreach ($this->data as $A => $valA)
        {
            // fill up the item's nearest neighbor database with infinity
            $this->nearest_neighbor[$A] = array_fill(0, $this->k, INF);

            // find k nearest neighbors (if 'tie', add one more neighbor)
            foreach ($this->data as $B => $valB)
            {
                // calculate distance between A and B
                $distance = $this->get_distance($valA, $valB);

                // if distance lower than highest seen distance
                if ($distance < end($this->nearest_neighbor[$A]))
                {
                    // remove last distance element of array, if no 'tie'
                    if (!in_array($distance, $this->nearest_neighbor[$A]))
                        array_pop($this->nearest_neighbor[$A]);

                    // push new A-B distance onto the end of array
                    $this->nearest_neighbor[$A][$B] = $distance;

                    // re-sort array by distances
                    asort($this->nearest_neighbor[$A]);
                }
            }
            // if infinite values are left at the end, remove them
            while (is_infinite(end($this->nearest_neighbor[$A])))
                array_pop($this->nearest_neighbor[$A]);
        }
    }

// ------------------------------------------------------------------ //

    # function: calculate reachability distance (rd)
    protected function reachability_distance($A, $B)
    {
        // calculate Euclidean distance between A and B
        $distance = $this->get_distance($this->data[$A], $this->data[$B]);
        // get distance of B to it's k nearest neighbor
        $k_distance = end($this->nearest_neighbor[$B]);
        // calculate reachability distance between A and B
        $rd = max($distance, $k_distance);

        return $rd;
    }

// ------------------------------------------------------------------ //

    # function: calculate local reachability density (lrd)
    protected function local_reachability_distance($A)
    {
        $sum = 1; // avoid division by zero if zero-distance
        // calculate sum of reachability distances for A-B
        foreach ($this->nearest_neighbor[$A] as $B => $distance)
            $sum += $this->reachability_distance($A, $B);

        // calculate local reachability density
        $lrd = 1 / ($sum / count(array_unique($this->nearest_neighbor[$A])));

        return $lrd;
    }

// ------------------------------------------------------------------ //

    # function: calculate local outlier factor (lof)
    protected function local_outlier_factor($A)
    {
        $sum = 0;
        // calculate sum of local reachability densities for A
        foreach ($this->nearest_neighbor[$A] as $B => $distance)
            $sum += $this->local_reachability_distance($B);

        // calculate local outlier factor
        $lof = ($sum / count(array_unique($this->nearest_neighbor[$A]))) / $this->local_reachability_distance($A);

        return $lof;
    }

// ------------------------------------------------------------------ //

    # function: get distance of values or geolocations
    protected function get_distance(&$valA, &$valB)
    {
        if ($this->is_values) // assume values (e.g. bytes-sent)
            return $this->get_distance_val($valA, $valB);
        else // assume arrays (in our case: geo-coordinates)
            return $this->get_distance_geo($valA, $valB);
    }

// ------------------------------------------------------------------ //

    # function: calculate distance of two values (e.g. bytes-sent)
    protected function get_distance_val($valA, $valB)
    {
        // try to get distance from cache
        if (isset($this->cache['distance'][$valA][$valB]))
            return $this->cache['distance'][$valA][$valB];

        // calculate absolute value of difference
        $distance = abs($valA - $valB);

        // add distance to cache
        $this->cache['distance'][$valA][$valB] = $distance;

        return $distance;
    }

// ------------------------------------------------------------------ //

    # function: calculate distance of two geolocations
    protected function get_distance_geo($valA, $valB)
    {
        // try to get distance from cache
        $a = implode(',', $valA); $b = implode(',', $valB);
        if (isset($this->cache['distance'][$a][$b]))
            return $this->cache['distance'][$a][$b];

        // get longitude/latitude for both items
        $lat1 = $valA[0]; $lon1 = $valA[1];
        $lat2 = $valB[0]; $lon2 = $valB[1];

        $earth_radius = 16371; // mean radius of the earth in km
        $deg_per_rad = 57.29578; // number of degrees/radian
        $distance = $earth_radius * pi() * sqrt(($lat1 - $lat2)
                        * ($lat1 - $lat2)
                        + cos($lat1 / $deg_per_rad)
                        * cos($lat2 / $deg_per_rad)
                        * ($lon1 - $lon2)
                        * ($lon1 - $lon2)) / 180;
        // add distance to cache
        $this->cache['distance'][$a][$b] = $distance;

        return $distance;
    }
}
?>