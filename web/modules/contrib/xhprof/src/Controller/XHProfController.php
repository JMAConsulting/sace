<?php

namespace Drupal\xhprof\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\xhprof\XHProfLib\Report\ReportConstants;
use Drupal\xhprof\XHProfLib\Report\ReportInterface;
use Drupal\xhprof\XHProfLib\Run;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Displays profiling results.
 */
class XHProfController extends ControllerBase {

  /**
   * The profiler.
   *
   * @var \Drupal\xhprof\ProfilerInterface
   */
  private $profiler;

  /**
   * The report engine.
   *
   * @var \Drupal\xhprof\XHProfLib\Report\ReportEngine
   */
  private $reportEngine;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  private $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->profiler = $container->get('xhprof.profiler');
    $instance->reportEngine = $container->get('xhprof.report_engine');
    $instance->dateFormatter = $container->get('date.formatter');
    return $instance;
  }

  /**
   * Returns list of runs.
   *
   * @return array
   *   A render array.
   */
  public function runsAction() {
    $runs = $run = $this->profiler->getStorage()->getRuns();

    $rows = [];
    foreach ($runs as $run) {
      $rows[] = [
        Link::createFromRoute($run['run_id'], 'xhprof.run', ['run' => $run['run_id']]),
        format_size($run['size']),
        isset($run['path']) ? $run['path'] : '',
        $this->dateFormatter->format($run['date'], 'small'),
      ];
    }

    return [
      'table' => [
        '#type' => 'table',
        '#header' => [
          ['data' => $this->t('View')],
          ['data' => $this->t('File size')],
          ['data' => $this->t('Path'), 'field' => 'path'],
          ['data' => $this->t('Date'), 'field' => 'date', 'sort' => 'desc'],
        ],
        '#rows' => $rows,
        '#empty' => $this->t('No runs collected'),
        '#attributes' => ['id' => 'xhprof-runs-table'],
      ],
    ];
  }

  /**
   * Renders the run.
   *
   * @param \Drupal\xhprof\XHProfLib\Run $run
   *   The run.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return array
   *   A render array.
   */
  public function runAction(Run $run, Request $request) {
    $length = $request->query->get('length', 100);
    $sort = $request->query->get('sort', 'wt');

    $report = $this->reportEngine->getReport(NULL, NULL, $run, NULL, NULL, $sort, NULL, NULL);

    $run_id = $run->getId();
    $build['#title'] = $this->t('XHProf view report for %id', ['%id' => $run_id]);

    $descriptions = ReportConstants::getDescriptions();

    $build['summary'] = [
      'table' => [
        '#type' => 'table',
        '#responsive' => FALSE,
        '#header' => [
            $this->t('Overall summary'),
            $this->t('%mu is <a href=":wiki">microseconds</a>', [
              '%mu' => 'μs',
              ':wiki' => 'https://en.wikipedia.org/wiki/Microsecond',
            ]),
        ],
        '#rows' => $this->getSummaryRows($report, $descriptions),
      ],
    ];

    $build['length'] = [
      '#type' => 'inline_template',
      '#template' => ($length == -1)
        ? '<h3>Displaying all functions, sorted by {{ sort }}. [{{ top }}]</h3>'
        : '<h3>Displaying top {{ length }} functions, sorted by {{ sort }}. [{{ all }}]</h3>',
      '#context' => [
        'length' => $length,
        'all' => Link::createFromRoute($this->t('show all'), 'xhprof.run', [
          'run' => $run_id,
          'length' => -1,
        ]),
        'top' => Link::createFromRoute($this->t('show top'), 'xhprof.run', [
          'run' => $run_id,
          'length' => 100,
        ]),
        'sort' => Xss::filter($descriptions[$sort], []),
      ],
    ];

    $build['table'] = [
      '#type' => 'table',
      '#responsive' => FALSE,
      '#sticky' => TRUE,
      '#header' => $this->getRunHeader($report, $descriptions, $run_id),
      '#rows' => $this->getRunRows($run, $report, $length),
      '#attributes' => ['class' => ['responsive']],
      '#attached' => [
        'library' => [
          'xhprof/xhprof',
        ],
      ],
      '#cache' => [
        'contexts' => ['url.query_args'],
      ],
    ];

    return $build;
  }

  /**
   * Renders diff of two runs.
   *
   * @param \Drupal\xhprof\XHProfLib\Run $run1
   *   The first run.
   * @param \Drupal\xhprof\XHProfLib\Run $run2
   *   The second run.
   *
   * @return array
   *   A render array.
   */
  public function diffAction(Run $run1, Run $run2) {
    return ['#markup' => 'Not working yet'];
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Run $run
   * @param $symbol
   *
   * @return array
   */
  public function symbolAction(Run $run, $symbol, Request $request) {
    $sort = $request->query->get('sort', 'wt');

    $globalReport = $this->reportEngine->getReport(NULL, NULL, $run, NULL, NULL, $sort, NULL, NULL);
    $report = $this->reportEngine->getReport(NULL, NULL, $run, NULL, [$symbol], $sort, NULL, NULL);

    $build['#title'] = $this->t('XHProf view report for %id', ['%id' => $run->getId()]);

    $descriptions = ReportConstants::getDescriptions();

    $build['title'] = [
      '#type' => 'inline_template',
      '#template' => '<strong>Parent/Child report for ' . $symbol . '</strong>',
    ];

    $build['table'] = [
      '#theme' => 'table',
      '#header' => $this->getRunHeader($report, $descriptions, $run->getId()),
      '#rows' => $this->getRunRows($run, $report, -1, $globalReport, $symbol),
      '#attributes' => ['class' => ['responsive']],
      '#attached' => [
        'library' => [
          'xhprof/xhprof',
        ],
      ],
    ];

    return $build;
  }

  /**
   * @param string $class
   *
   * @return \Drupal\Component\Render\FormattableMarkup
   */
  private function abbrClass($class) {
    $parts = explode('\\', $class);
    $short = array_pop($parts);

    if (strlen($short) >= 40) {
      $short = substr($short, 0, 30) . " … " . substr($short, -5);
    }

    return new FormattableMarkup('<abbr title="@class">@short</abbr>', [
      '@class' => $class,
      '@short' => $short,
    ]);
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Report\ReportInterface $report
   * @param array $descriptions
   *
   * @return array
   */
  private function getRunHeader(ReportInterface $report, $descriptions, $run_id) {
    $headers = ['fn', 'ct', 'ct_perc'];

    $metrics = $report->getMetrics();

    foreach ($metrics as $metric) {
      $headers[] = $metric;
      $headers[] = $metric . '_perc';
      $headers[] = 'excl_' . $metric;
      $headers[] = 'excl_' . $metric . '_perc';
    }

    $sortable = ReportConstants::getSortableColumns();
    foreach ($headers as &$header) {
      if (isset($sortable[$header])) {
        $header = [
          'data' => Link::createFromRoute($descriptions[$header], 'xhprof.run', ['run' => $run_id], [
            'query' => [
              'sort' => $header,
            ],
          ])->toRenderable(),
        ];
      }
      else {
        $header = new FormattableMarkup($descriptions[$header], []);
      }
    }

    return $headers;
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Run $run
   * @param \Drupal\xhprof\XHProfLib\Report\ReportInterface $report
   * @param int $length
   *
   * @return array
   */
  private function getRunRows(Run $run, ReportInterface $report, $length, ReportInterface $globalReport = NULL, $symbol = NULL) {
    $rows = [];

    $runId = $run->getId();
    $symbols = $report->getSymbols($length);

    if ($symbol) {
      $globalSymbols = $globalReport->getSymbols(-1);

      // Add the current function in the table.
      $this->getCurrentFunctionRows($globalSymbols[$symbol], $rows);

      // Add parent functions in the table.
      $runSymbols = $run->getSymbols();
      $parents = [];
      $children = [];
      foreach ($runSymbols as $value) {
        if (($value->getChild() == $symbol) && ($parent = $value->getParent())) {
          $parents[$parent] = $globalSymbols[$parent];
        }
        elseif (($value->getParent() == $symbol) && ($child = $value->getChild())) {
          $children[$child] = $value;
        }
      }
      $this->getParentFunctionsRows($parents, $runId, $rows);

      if (\count($children)) {
        $columns = \current($symbols);
        $rows[] = [
          [
            'data' => new FormattableMarkup('<strong>@value</strong>', [
              '@value' => $this->formatPlural(\count($children), 'Child function', 'Child functions (@count)'),
            ]),
            'colspan' => \count($columns),
          ],
        ];
      }
    }

    foreach ($symbols as $value) {
      // If its a symbol table, display only the children in the list.
      if (!$symbol || !empty($children[$value[0]])) {
        $text = $value[0];
        $url = Url::fromRoute('xhprof.symbol', [
          'run' => $runId,
          'symbol' => $value[0],
        ]);

        $value[0] = Link::fromTextAndUrl($text, $url)->toString();

        $rows[] = $value;
      }
    }

    return $rows;
  }

  private function getCurrentFunctionRows($symbol, &$rows) {
    $rows[] = [
      [
        'data' => new FormattableMarkup('<strong>@value</strong>', [
          '@value' => $this->t('Current function'),
        ]),
        'colspan' => \count($symbol),
      ],
    ];

    $symbol[0] = Link::fromTextAndUrl($symbol[0], Url::fromRoute('<current>'));
    $rows[] = $symbol;

    return $rows;
  }

  private function getParentFunctionsRows($parents, $runId, &$rows) {
    if (!empty($parents)) {
      $columns = \current($parents);
      $rows[] = [
        [
          'data' => new FormattableMarkup('<strong>@value</strong>', [
            '@value' => $this->formatPlural(\count($parents), 'Parent function', 'Parent functions (@count)'),
          ]),
          'colspan' => \count($columns),
        ],
      ];
      foreach ($parents as $parent) {
        $parent[0] = Link::fromTextAndUrl($parent[0], Url::fromRoute('xhprof.symbol', [
          'run' => $runId,
          'symbol' => $parent[0],
        ]));

        $rows[] = $parent;
      }
    }

    return $rows;
  }

  /**
   * @param \Drupal\xhprof\XHProfLib\Report\ReportInterface $report
   * @param array $descriptions
   *
   * @return array
   */
  private function getSummaryRows(ReportInterface $report, $descriptions) {
    $summaryRows = [];
    $possibileMetrics = $report->getPossibleMetrics();
    foreach ($report->getSummary() as $metric => $value) {
      $key = 'Total ' . Xss::filter($descriptions[$metric], []);
      $unit = isset($possibileMetrics[$metric]) ? $possibileMetrics[$metric][1] : '';

      $value = new FormattableMarkup('@value @unit', [
        '@value' => $value,
        '@unit' => $unit,
      ]);

      $summaryRows[] = [$key, $value];
    }

    return $summaryRows;
  }

}
