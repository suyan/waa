<?php
/*
 * @Author: Su Yan <http://yansu.org>
 * @Date:   2014-03-22 18:39:48
 * @Last Modified by:   Su Yan
 * @Last Modified time: 2014-03-22 22:39:58
*/
namespace Suyan\Lorg\Input;
interface InputInterface {
    public function init($source = '');
    public function resetInput();
    public function getLine();
}
