<?php

namespace Drupal\xhprof\XHProfLib\Report;

/**
 * Provides helpers for string constants.
 */
class ReportConstants {

  /**
   * Returns collumns to allow sorting.
   *
   * @return array
   *   Keyed array of indicators.
   */
  public static function getSortableColumns() {
    return [
      "fn" => 1,
      "ct" => 1,
      "wt" => 1,
      "excl_wt" => 1,
      "ut" => 1,
      "excl_ut" => 1,
      "st" => 1,
      "excl_st" => 1,
      "mu" => 1,
      "excl_mu" => 1,
      "pmu" => 1,
      "excl_pmu" => 1,
      "cpu" => 1,
      "excl_cpu" => 1,
      "samples" => 1,
      "excl_samples" => 1,
    ];
  }

  /**
   * Returns mapping of indicators description.
   *
   * @return array
   *   Keyed array of indicator and its description.
   */
  public static function getDescriptions() {
    return [
      "fn" => "Function Name",
      "ct" => "Calls",
      "ct_perc" => "Calls%",
      "wt" => "Incl. Wall Time (μs)",
      "wt_perc" => "IWall%",
      "excl_wt" => "Excl. Wall Time (μs)",
      "excl_wt_perc" => "EWall%",
      "ut" => "Incl. User (μs)",
      "ut_perc" => "IUser%",
      "excl_ut" => "Excl. User (μs)",
      "excl_ut_perc" => "EUser%",
      "st" => "Incl. Sys (μs)",
      "st_perc" => "ISys%",
      "excl_st" => "Excl. Sys (μs)",
      "excl_st_perc" => "ESys%",
      "cpu" => "Incl. CPU (μs)",
      "cpu_perc" => "ICpu%",
      "excl_cpu" => "Excl. CPU (μs)",
      "excl_cpu_perc" => "ECPU%",
      "mu" => "Incl. MemUse (bytes)",
      "mu_perc" => "IMemUse%",
      "excl_mu" => "Excl. MemUse (bytes)",
      "excl_mu_perc" => "EMemUse%",
      "pmu" => "Incl. PeakMemUse (bytes)",
      "pmu_perc" => "IPeakMemUse%",
      "excl_pmu" => "Excl. PeakMemUse (bytes)",
      "excl_pmu_perc" => "EPeakMemUse%",
      "samples" => "Incl. Samples",
      "samples_perc" => "ISamples%",
      "excl_samples" => "Excl. Samples",
      "excl_samples_perc" => "ESamples%",
    ];
  }

  /**
   * Returns mapping for diff indicator' descritions.
   *
   * @return array
   *   Keyed array of indicator and its description.
   */
  public static function getDiffDescriptions() {
    return [
      "fn" => "Function Name",
      "ct" => "Calls Diff",
      "Calls%" => "Calls<br>Diff%",
      "wt" => "Incl. Wall<br>Diff<br>(μs)",
      "IWall%" => "IWall<br> Diff%",
      "excl_wt" => "Excl. Wall<br>Diff<br>(μs)",
      "EWall%" => "EWall<br>Diff%",
      "ut" => "Incl. User Diff<br>(μs)",
      "IUser%" => "IUser<br>Diff%",
      "excl_ut" => "Excl. User<br>Diff<br>(μs)",
      "EUser%" => "EUser<br>Diff%",
      "cpu" => "Incl. CPU Diff<br>(μs)",
      "ICpu%" => "ICpu<br>Diff%",
      "excl_cpu" => "Excl. CPU<br>Diff<br>(μs)",
      "ECpu%" => "ECpu<br>Diff%",
      "st" => "Incl. Sys Diff<br>(μs)",
      "ISys%" => "ISys<br>Diff%",
      "excl_st" => "Excl. Sys Diff<br>(μs)",
      "ESys%" => "ESys<br>Diff%",
      "mu" => "Incl.<br>MemUse<br>Diff<br>(bytes)",
      "IMUse%" => "IMemUse<br>Diff%",
      "excl_mu" => "Excl.<br>MemUse<br>Diff<br>(bytes)",
      "EMUse%" => "EMemUse<br>Diff%",
      "pmu" => "Incl.<br> PeakMemUse<br>Diff<br>(bytes)",
      "IPMUse%" => "IPeakMemUse<br>Diff%",
      "excl_pmu" => "Excl.<br>PeakMemUse<br>Diff<br>(bytes)",
      "EPMUse%" => "EPeakMemUse<br>Diff%",
      "samples" => "Incl. Samples Diff",
      "ISamples%" => "ISamples Diff%",
      "excl_samples" => "Excl. Samples Diff",
      "ESamples%" => "ESamples Diff%",
    ];
  }

  /**
   * Returns mapping of formatting callbacks.
   *
   * @return array
   *   Keyed array of indicators and optional callable to format them.
   */
  public static function getFormatCbk() {
    return [
      "fn" => "",
      "ct" => [__CLASS__, "countFormat"],
      "ct_perc" => [__CLASS__, "percentFormat"],
      "wt" => "number_format",
      "wt_perc" => [__CLASS__, "percentFormat"],
      "excl_wt" => "number_format",
      "excl_wt_perc" => [__CLASS__, "percentFormat"],
      "ut" => "number_format",
      "ut_perc" => [__CLASS__, "percentFormat"],
      "excl_ut" => "number_format",
      "excl_ut_perc" => [__CLASS__, "percentFormat"],
      "st" => "number_format",
      "st_perc" => [__CLASS__, "percentFormat"],
      "excl_st" => "number_format",
      "excl_st_perc" => [__CLASS__, "percentFormat"],
      "cpu" => "number_format",
      "cpu_perc" => [__CLASS__, "percentFormat"],
      "excl_cpu" => "number_format",
      "excl_cpu_perc" => [__CLASS__, "percentFormat"],
      "mu" => "number_format",
      "mu_perc" => [__CLASS__, "percentFormat"],
      "excl_mu" => "number_format",
      "excl_mu_perc" => [__CLASS__, "percentFormat"],
      "pmu" => "number_format",
      "pmu_perc" => [__CLASS__, "percentFormat"],
      "excl_pmu" => "number_format",
      "excl_pmu_perc" => [__CLASS__, "percentFormat"],
      "samples" => "number_format",
      "samples_perc" => [__CLASS__, "percentFormat"],
      "excl_samples" => "number_format",
      "excl_samples_perc" => [__CLASS__, "percentFormat"],
    ];
  }

  /**
   * Formats float numbers.
   *
   * @param float $num
   *   The number.
   *
   * @return string
   *   The formatted number.
   */
  public static function countFormat($num) {
    $num = round($num, 3);
    if (round($num) == $num) {
      return number_format($num);
    }
    return number_format($num, 3);
  }

  /**
   * Formats float as percent.
   *
   * @param float $s
   *   The number.
   * @param int $precision
   *   The precision.
   *
   * @return string
   *   The formatted number.
   */
  public static function percentFormat($s, $precision = 1) {
    return sprintf('%.' . $precision . 'f%%', 100 * $s);
  }

}
