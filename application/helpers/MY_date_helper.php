<?php

    // -----------------------------------------------------------------

    function get_month_begin($timestamp='') {

        $timestamp = empty($timestamp)? time() : $timestamp;

        $result = date('Y-m-01 00:00:00', $timestamp);

        return $result;

    }

    // -----------------------------------------------------------------

    function get_month_end($timestamp='') {

        $timestamp = empty($timestamp)? time() : $timestamp;

        $days_in_month = days_in_month(date('m', $timestamp), date('Y', $timestamp));

        $result = date('Y-m-' . $days_in_month . ' 23:59:59', $timestamp);

        return $result;

    }

    // -----------------------------------------------------------------

    function get_year_begin($timestamp='') {

        $timestamp = empty($timestamp)? time() : $timestamp;

        $result = date('Y-01-01 00:00:00', $timestamp);

        return $result;

    }

    // -----------------------------------------------------------------

    function get_year_end($timestamp='') {

        $timestamp = empty($timestamp)? time() : $timestamp;

        $result = date('Y-12-31 23:59:59', $timestamp);

        return $result;

    }

?>
