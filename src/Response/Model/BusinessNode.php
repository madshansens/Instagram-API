<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getAllFollowersAgeGraph()
 * @method mixed getAverageEngagementCount()
 * @method mixed getFollowersCount()
 * @method mixed getFollowersDeltaFromLastWeek()
 * @method mixed getFollowersTopCitiesGraph()
 * @method mixed getFollowersUnitState()
 * @method mixed getGenderGraph()
 * @method mixed getLastWeekCall()
 * @method mixed getLastWeekEmail()
 * @method mixed getLastWeekGetDirection()
 * @method mixed getLastWeekImpressions()
 * @method mixed getLastWeekImpressionsDayGraph()
 * @method mixed getLastWeekProfileVisits()
 * @method mixed getLastWeekProfileVisitsDayGraph()
 * @method mixed getLastWeekReach()
 * @method mixed getLastWeekReachDayGraph()
 * @method mixed getLastWeekText()
 * @method mixed getLastWeekWebsiteVisits()
 * @method mixed getPostsCount()
 * @method mixed getPostsDeltaFromLastWeek()
 * @method mixed getState()
 * @method mixed getWeekOverWeekCall()
 * @method mixed getWeekOverWeekEmail()
 * @method mixed getWeekOverWeekGetDirection()
 * @method mixed getWeekOverWeekImpressions()
 * @method mixed getWeekOverWeekProfileVisits()
 * @method mixed getWeekOverWeekReach()
 * @method mixed getWeekOverWeekText()
 * @method mixed getWeekOverWeekWebsiteVisits()
 * @method mixed get_SummaryPoststYGwD()
 * @method mixed get_SummaryPromotions2ubm1F()
 * @method mixed get_SummaryStoriesjmsA2()
 * @method mixed get_TodayHourlyGraph2Iuh8n()
 * @method mixed get__Typename()
 * @method bool isAllFollowersAgeGraph()
 * @method bool isAverageEngagementCount()
 * @method bool isFollowersCount()
 * @method bool isFollowersDeltaFromLastWeek()
 * @method bool isFollowersTopCitiesGraph()
 * @method bool isFollowersUnitState()
 * @method bool isGenderGraph()
 * @method bool isLastWeekCall()
 * @method bool isLastWeekEmail()
 * @method bool isLastWeekGetDirection()
 * @method bool isLastWeekImpressions()
 * @method bool isLastWeekImpressionsDayGraph()
 * @method bool isLastWeekProfileVisits()
 * @method bool isLastWeekProfileVisitsDayGraph()
 * @method bool isLastWeekReach()
 * @method bool isLastWeekReachDayGraph()
 * @method bool isLastWeekText()
 * @method bool isLastWeekWebsiteVisits()
 * @method bool isPostsCount()
 * @method bool isPostsDeltaFromLastWeek()
 * @method bool isState()
 * @method bool isWeekOverWeekCall()
 * @method bool isWeekOverWeekEmail()
 * @method bool isWeekOverWeekGetDirection()
 * @method bool isWeekOverWeekImpressions()
 * @method bool isWeekOverWeekProfileVisits()
 * @method bool isWeekOverWeekReach()
 * @method bool isWeekOverWeekText()
 * @method bool isWeekOverWeekWebsiteVisits()
 * @method bool is_SummaryPoststYGwD()
 * @method bool is_SummaryPromotions2ubm1F()
 * @method bool is_SummaryStoriesjmsA2()
 * @method bool is_TodayHourlyGraph2Iuh8n()
 * @method bool is__Typename()
 * @method setAllFollowersAgeGraph(mixed $value)
 * @method setAverageEngagementCount(mixed $value)
 * @method setFollowersCount(mixed $value)
 * @method setFollowersDeltaFromLastWeek(mixed $value)
 * @method setFollowersTopCitiesGraph(mixed $value)
 * @method setFollowersUnitState(mixed $value)
 * @method setGenderGraph(mixed $value)
 * @method setLastWeekCall(mixed $value)
 * @method setLastWeekEmail(mixed $value)
 * @method setLastWeekGetDirection(mixed $value)
 * @method setLastWeekImpressions(mixed $value)
 * @method setLastWeekImpressionsDayGraph(mixed $value)
 * @method setLastWeekProfileVisits(mixed $value)
 * @method setLastWeekProfileVisitsDayGraph(mixed $value)
 * @method setLastWeekReach(mixed $value)
 * @method setLastWeekReachDayGraph(mixed $value)
 * @method setLastWeekText(mixed $value)
 * @method setLastWeekWebsiteVisits(mixed $value)
 * @method setPostsCount(mixed $value)
 * @method setPostsDeltaFromLastWeek(mixed $value)
 * @method setState(mixed $value)
 * @method setWeekOverWeekCall(mixed $value)
 * @method setWeekOverWeekEmail(mixed $value)
 * @method setWeekOverWeekGetDirection(mixed $value)
 * @method setWeekOverWeekImpressions(mixed $value)
 * @method setWeekOverWeekProfileVisits(mixed $value)
 * @method setWeekOverWeekReach(mixed $value)
 * @method setWeekOverWeekText(mixed $value)
 * @method setWeekOverWeekWebsiteVisits(mixed $value)
 * @method set_SummaryPoststYGwD(mixed $value)
 * @method set_SummaryPromotions2ubm1F(mixed $value)
 * @method set_SummaryStoriesjmsA2(mixed $value)
 * @method set_TodayHourlyGraph2Iuh8n(mixed $value)
 * @method set__Typename(mixed $value)
 */
class BusinessNode extends AutoPropertyHandler
{
    public $__typename;
    public $followers_count;
    public $followers_delta_from_last_week;
    public $posts_count;
    public $posts_delta_from_last_week;
    public $last_week_impressions;
    public $week_over_week_impressions;
    public $last_week_reach;
    public $week_over_week_reach;
    public $last_week_profile_visits;
    public $week_over_week_profile_visits;
    public $last_week_website_visits;
    public $week_over_week_website_visits;
    public $last_week_call;
    public $week_over_week_call;
    public $last_week_text;
    public $week_over_week_text;
    public $last_week_email;
    public $week_over_week_email;
    public $last_week_get_direction;
    public $week_over_week_get_direction;
    public $average_engagement_count;
    public $last_week_impressions_day_graph;
    public $last_week_reach_day_graph;
    public $last_week_profile_visits_day_graph;
    public $_summary_poststYGwD;
    public $state;
    public $_summary_storiesjmsA2;
    public $followers_unit_state;
    public $_today_hourly_graph2Iuh8n;
    public $gender_graph;
    public $all_followers_age_graph;
    public $followers_top_cities_graph;
    public $_summary_promotions2ubm1F;
}
