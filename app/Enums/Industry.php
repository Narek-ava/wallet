<?php


namespace App\Enums;


class Industry extends Enum
{

    //industry types constants
    const INDUSTRY_AGRICULTURE = 1;
    const INDUSTRY_MINING_QUARRYING = 2;
    const INDUSTRY_MANUFACTURE = 3;
    const INDUSTRY_REPAIRING_MACHINERY = 4;
    const INDUSTRY_ELECTRICITY_SUPPLYING = 5;
    const INDUSTRY_WATER_SUPPLYING = 6;
    const INDUSTRY_CONSTRUCTION = 7;
    const INDUSTRY_WHOLESALE_AND_RETAIL_TRADE = 8;
    const INDUSTRY_TRANSPORTATION = 9;
    const INDUSTRY_ACCOMMODATION_AND_FOOD_SERVICE = 10;
    const INDUSTRY_PUBLISHING_AND_BROADCASTING = 11;
    const INDUSTRY_TELECOMMUNICATION = 12;
    const INDUSTRY_IT = 13;
    const INDUSTRY_FINANCIAL = 14;
    const INDUSTRY_REAL_ESTATE = 15;
    const INDUSTRY_LEGAL_ACCOUNTING_MANAGEMENT = 16;
    const INDUSTRY_SCIENTIFIC_RESEARCH = 17;
    const INDUSTRY_OTHER_PROFESSIONAL = 18;
    const INDUSTRY_ADMINISTRATIVE_AND_SUPPORT = 19;
    const INDUSTRY_DEFENCE = 20;
    const INDUSTRY_EDUCATION = 21;
    const INDUSTRY_HUMAN_HEALTH = 22;
    const INDUSTRY_SOCIAL_WORK = 23;
    const INDUSTRY_ARTS = 24;
    const INDUSTRY_OTHER_SERVICE = 25;
    const INDUSTRY_ACTIVITY_OF_HOUSEHOLDS = 26;
    const INDUSTRY_ACTIVITY_OF_EXTRATERRITORIAL_ORGANIZATIONS = 27;



    const NAMES = [
        self::INDUSTRY_AGRICULTURE => 'enum_industry_agriculture',
        self::INDUSTRY_MINING_QUARRYING => 'enum_industry_mining_quarrying',
        self::INDUSTRY_MANUFACTURE => 'enum_industry_manufacture',
        self::INDUSTRY_REPAIRING_MACHINERY => 'enum_industry_repairing_machinery',
        self::INDUSTRY_ELECTRICITY_SUPPLYING => 'enum_industry_electricity_supplying',
        self::INDUSTRY_WATER_SUPPLYING => 'enum_industry_water_supplying',
        self::INDUSTRY_CONSTRUCTION => 'enum_industry_construction',
        self::INDUSTRY_WHOLESALE_AND_RETAIL_TRADE => 'enum_industry_wholesale_and_retail_trade',
        self::INDUSTRY_TRANSPORTATION => 'enum_industry_transportation',
        self::INDUSTRY_ACCOMMODATION_AND_FOOD_SERVICE => 'enum_industry_accommodation_and_food_service',
        self::INDUSTRY_PUBLISHING_AND_BROADCASTING => 'enum_industry_publishing_and_broadcasting',
        self::INDUSTRY_TELECOMMUNICATION => 'enum_industry_telecommunication',
        self::INDUSTRY_IT => 'enum_industry_it',
        self::INDUSTRY_FINANCIAL => 'enum_industry_financial',
        self::INDUSTRY_REAL_ESTATE => 'enum_industry_real_estate',
        self::INDUSTRY_LEGAL_ACCOUNTING_MANAGEMENT => 'enum_industry_legal_accounting_management',
        self::INDUSTRY_SCIENTIFIC_RESEARCH => 'enum_industry_scientific_research',
        self::INDUSTRY_OTHER_PROFESSIONAL => 'enum_industry_other_professional',
        self::INDUSTRY_ADMINISTRATIVE_AND_SUPPORT => 'enum_industry_administrative_and_support',
        self::INDUSTRY_DEFENCE => 'enum_industry_defence',
        self::INDUSTRY_EDUCATION => 'enum_industry_education',
        self::INDUSTRY_HUMAN_HEALTH => 'enum_industry_human_health',
        self::INDUSTRY_SOCIAL_WORK => 'enum_industry_social_work',
        self::INDUSTRY_ARTS => 'enum_industry_arts',
        self::INDUSTRY_OTHER_SERVICE=> 'enum_industry_other_service',
        self::INDUSTRY_ACTIVITY_OF_HOUSEHOLDS => 'enum_industry_activity_of_households',
        self::INDUSTRY_ACTIVITY_OF_EXTRATERRITORIAL_ORGANIZATIONS => 'enum_industry_activity_of_extraterritorial_organizations',
    ];
}
