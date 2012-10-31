<?php
namespace Wednesday\Backbone;

/**
 * @Wednesday:Restable:Entity
 * class Entity
 *
 * 
 * This interface is not necessary but can be implemented for
 * Domain Objects which in some cases needs to be identified as Restable
 *
 * @version $Id: 1.8.7 RC2 wednesday $    $Id: 1.8.7 RC2 jameshelly $
  @author James A Helly <james@wednesday-london.com>
 * @package Wednesday.Restable
 * @subpackage Restable
 * @link http://cms.wednesday-london.com/
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
interface Entity
{
    /**
     * createAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function post($reqVars);

    /**
     * deleteAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function head($reqVars);

    /**
     * readAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function get($reqVars);

    /**
     * updateAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function put($reqVars);

    /**
     * deleteAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function delete($reqVars);

    /**
     * deleteAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function options($reqVars);

    /**
     * toJsonObject
     *
     * return Object
     */
    public function toJsonObject($nested = false);

    /**
     * toArray
     *
     * return Object
     */
    public function toArray($short = false, $nested = false);

}