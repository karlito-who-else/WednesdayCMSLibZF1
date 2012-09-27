<?php

namespace Wednesday\Lucenable;

/**
 * Description of Lucenable
 * This interface is not necessary but can be implemented for
 * Entities which in some cases needs to be identified as
 * Lucenable
 *
 * @version    $Id: 1.7.4 RC1 jameshelly $
 * @author mrhelly
 * @package Wednesday.Lucenable
 * @subpackage Lucenable
 */
interface Lucenable {
    // use now annotations instead of predifined methods, this interface is not necessary

    /**
     * @WED\Lucenable
     * to mark the field as lucenable use property annotation @WED\Lucenable
     * this field value will be included in built slug
     */

    /**
     * @WED\LuceneIndex - to mark property which will hold slug use annotation @gedmo:Slug
     * available options:
     *         indexes (optional, default="default") - indexes to add / update.
     *         follow (optional, default=false) - follow associations.
     *         type (optional, default="keyword") -
     *              "keyword" - Field is not tokenized, but is indexed and stored within the index.
     *              "unindexed" - Field is not tokenized nor indexed, but is stored in the index.
     *              "binary" - Binary String valued Field that is not tokenized nor indexed, but is stored in the index.
     *              "text" - Field is tokenized and indexed, and is stored in the index.
     *              "unstored" - Field is tokenized and indexed, but is not stored in the index.
     *
     * example:
     *
     * @WED\LuceneIndex(type="text", indexes="default,category", follow=false)
     * @Column(type="string", length=64)
     * $property
     */
}
