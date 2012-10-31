<?php
namespace Wednesday\Models;

/**
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
interface CoreEntityInterface
{

    /**
     * Wednesday\Restable\Entity
     * class Entity
     */

    /**
     * deleteAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function options($reqVars);

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
     * createAction
     *
     * @param array $regVars
     * @return boolean $retval
     */
    public function post($reqVars);

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
     * toArray
     *
     * return Object
     */
    public function toArray($short = false, $nested = false, $filters = false);

    /**
     * toJsonObject
     *
     * return Object
     */
    public function toJsonObject($nested = false, $short = false, $filters = false);

}