<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getAspectRatio()
 * @method mixed getAutoplay()
 * @method mixed getNumColumns()
 * @method mixed getTotalNumColumns()
 * @method bool isAspectRatio()
 * @method bool isAutoplay()
 * @method bool isNumColumns()
 * @method bool isTotalNumColumns()
 * @method setAspectRatio(mixed $value)
 * @method setAutoplay(mixed $value)
 * @method setNumColumns(mixed $value)
 * @method setTotalNumColumns(mixed $value)
 */
class ExploreItemInfo extends AutoPropertyHandler
{
    public $num_columns;
    public $total_num_columns;
    public $aspect_ratio;
    public $autoplay;
}
