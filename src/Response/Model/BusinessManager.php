<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method BusinessNode getAccountInsightsUnit()
 * @method PromotionsUnit getPromotionsUnit()
 * @method BusinessFeed get_Feed2py0Z1()
 * @method bool isAccountInsightsUnit()
 * @method bool isPromotionsUnit()
 * @method bool is_Feed2py0Z1()
 * @method setAccountInsightsUnit(BusinessNode $value)
 * @method setPromotionsUnit(PromotionsUnit $value)
 * @method set_Feed2py0Z1(BusinessFeed $value)
 */
class BusinessManager extends AutoPropertyHandler
{
    /**
     * @var PromotionsUnit
     */
    public $promotions_unit;
    /**
     * @var BusinessNode
     */
    public $account_insights_unit;
    /**
     * @var BusinessFeed
     */
    public $_feed2py0Z1;
}
