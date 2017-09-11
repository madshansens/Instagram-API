<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method BusinessEdges[] getEdges()
 * @method BusinessPageInfo getPageInfo()
 * @method bool isEdges()
 * @method bool isPageInfo()
 * @method setEdges(BusinessEdges[] $value)
 * @method setPageInfo(BusinessPageInfo $value)
 */
class SummaryPromotions extends AutoPropertyHandler
{
    /**
     * @var BusinessEdges[]
     */
    public $edges;
    /**
     * @var BusinessPageInfo
     */
    public $page_info;
}
