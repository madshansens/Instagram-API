<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class BusinessManager extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'promotions_unit'       => 'PromotionsUnit',
        'account_insights_unit' => 'BusinessNode',
        '_feed2py0Z1'           => 'BusinessFeed',
    ];
}
