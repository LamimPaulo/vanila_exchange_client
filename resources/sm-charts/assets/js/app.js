var smcPathToAssets = smcPathToAssets || '';
var smcGlobals = {
  debug: false,
  code: 'smc',
  pluginUrl: smcPathToAssets + '/sm-charts/',
  ajaxUrl: smcPathToAssets + '/sm-charts/ajax.php',
  ajaxGetData: 'ajaxGetData',
  ajaxSymbolAutocomplete: 'ajaxSymbolAutocomplete',
  lang: 'en',
  text: ''
};

"use strict";
var stockMarketChartsPlugin = (function ($, am) {
  log('smcGlobals', smcGlobals);
  var code = smcGlobals.code; // plugin code
  var classAssetAutocomplete = code + '-asset-autocomplete';

  // translate amCharts export menu
  if (typeof smcGlobals.lang != 'undefined' && typeof smcGlobals.text.amCharts != 'undefined' && typeof smcGlobals.text.amCharts.export != 'undefined')
    am.translations['export'][smcGlobals.lang] = smcGlobals.text.amCharts.export;

  // translate amCharts data loader messages
  if (typeof smcGlobals.lang != 'undefined' && typeof smcGlobals.text.amCharts != 'undefined' && typeof smcGlobals.text.amCharts.dataLoader != 'undefined')
    am.translations.dataLoader[smcGlobals.lang] = smcGlobals.text.amCharts.dataLoader;

  function buildChart(containerId, assetSymbol, compareAssetSymbols, range, interval, chartSettings) {
    var chart;
    var loadedComparisonSeries = [];
    var comparisonSelectSymbols = compareAssetSymbols ? compareAssetSymbols.split(',') : [];
    var $chartContainer = $('#'+containerId);
    var utcHours = new Date().getUTCHours();
    var utcMinutes = new Date().getUTCMinutes();
    var supportedPeriods = {
      '1D': {period: 'mm', count: 300},
      '1W': {period: 'DD', count: 7},
      '2W': {period: 'DD', count: 14},
      '1M': {period: 'MM', count: 1},
      '3M': {period: 'MM', count: 3},
      '6M': {period: 'MM', count: 6},
      'YTD':{period: 'YTD'},
      '1Y': {period: 'YYYY', count: 1},
      '2Y': {period: 'YYYY', count: 2},
      '5Y': {period: 'YYYY', count: 5},
      '10Y': {period: 'YYYY', count: 10},
      'ALL':{period: 'MAX'}
    };
    var searchMode = chartSettings && typeof chartSettings.searchMode !== 'undefined'
      ? chartSettings.searchMode
      : 'comparison'

    // add translated labels to periods if available
    for (var period in supportedPeriods) {
      if (supportedPeriods.hasOwnProperty(period)) {
        if (typeof smcGlobals.text.amCharts != 'undefined' && typeof smcGlobals.text.amCharts.periods != 'undefined') {
          supportedPeriods[period].label = smcGlobals.text.amCharts.periods[period];
        } else {
          supportedPeriods[period].label = period;
        }
      }
    }

    chartSettings = chartSettings || {};
    log('chartSettings', assetSymbol, range, interval, chartSettings);

    $chartContainer.css({
      width:      chartSettings.width || '100%',
      height:     chartSettings.height || '500px',
      fontSize:   chartSettings.fontSize || 14,
      color:      chartSettings.color || '#383838',
      background: chartSettings.backgroundColor || '#fff'
    });

    var assetAutocompleteOptions = searchMode === 'comparison' && comparisonSelectSymbols.length
        ? comparisonSelectSymbols.map(function(symbol) { return '<option value="' + symbol + '" selected="selected">' + symbol + '</option>' })
        : (searchMode === 'search'
            ? '<option value="' + assetSymbol + '" selected="selected">' + assetSymbol + '</option>'
            : '');

    $chartContainer.prepend(
        '<div class="' + code + '-chart-comparison">' +
        '<select class="' + classAssetAutocomplete + '"' + (searchMode === 'comparison' ? '" multiple="multiple"' : '') + '>' +
        assetAutocompleteOptions +
        '</select>' +
        '</div>' +
        '<div id="' + containerId + '-chart" class="' + code + '-chart"></div>'
    );

    var $classAssetAutocomplete = $chartContainer.find('.' + classAssetAutocomplete);

    var chartOptions = {
      type: 'stock',
      language: smcGlobals.lang || 'en',
      mouseWheelScrollEnabled:  chartSettings.mouseWheelZoomEnabled || false,

      categoryAxesSettings: {
        minPeriod:            'mm',
        color:                chartSettings.color || '#383838', // text color
        gridColor:            chartSettings.gridColor || '#e0e0e0', // vertical grid line color
        gridAlpha:            chartSettings.gridAlpha || 0.8, // vertical grid line alpha
        gridThickness:        typeof chartSettings.gridThickness != 'undefined' ? chartSettings.gridThickness : 1, // vertical grid line thickness
        groupToPeriods:       ['15mm','30mm','DD','WW','MM'],
        equalSpacing:         true, // skip time gaps
        minHorizontalGap:     100,
        autoGridCount:        true,
        dateFormats:      [
          {period:'fff',format:'JJ:NN:SS'},
          {period:'ss',format:'JJ:NN:SS'},
          {period:'mm',format:'JJ:NN'},
          {period:'hh',format:'JJ:NN'},
          {period:'DD',format:'DD MMM'},
          {period:'WW',format:'DD MMM'},
          {period:'MM',format:'MMM YY'},
          {period:'YYYY',format:'YYYY'}
        ]
      },

      dataSets: [{
        dataLoader: {
          url: getAjaxUrl (assetSymbol, range, interval),
          format: 'json',
          postProcess: function(response, config, chart) {
            log('response', response);
            return response.data;
          }
        },
        title: getDataSetTitle(assetSymbol),
        fieldMappings: [{
          fromField: 'value',
          toField: 'value'
        }, {
          fromField: 'volume',
          toField: 'volume'
        }],
        categoryField: 'time'
      }],

      panelsSettings: {
        usePrefixes:            chartSettings.usePrefixes || false, // if true prefixes will be used for big and small numbers.
        fontSize:               chartSettings.fontSize || 14,
        marginTop:              chartSettings.marginTop || 0,
        marginRight:            chartSettings.marginRight || 10,
        marginBottom:           chartSettings.marginBottom || 0,
        marginLeft:             chartSettings.marginLeft || 10,
        backgroundColor:        chartSettings.backgroundColor, // this is required for export to work as it doesn't take into account background set in CSS
        backgroundAlpha:        0,
        startDuration:          0, // enabling animation causes an issue with background logo, it disappears after switching between data periods
        thousandsSeparator:     chartSettings.thousandsSeparator || ',',
        decimalSeparator:       chartSettings.decimalSeparator || '.',
        precision:              chartSettings.precision || 2,
        percentPrecision:       chartSettings.precision || 2,
        creditsPosition:        'bottom-left'
      },

      panels: [{
        showCategoryAxis:     true,
        title:                chartSettings.primaryPanelTitle || 'Price',
        percentHeight:        chartSettings.secondaryChartType != 'none' ? 70 : 100,
        drawingIconsEnabled:  true,
        eraseAll:             true,
        stockGraphs: [{
          id: 'mainGraph',
          type:                       chartSettings.primaryChartType || 'smoothedLine',
          valueField:                 'value',
          lineColor:                  chartSettings.primaryLineColor || '#00842c',
          fillAlphas:                 chartSettings.primaryLineColorAlpha || 0.15,
          lineThickness:              typeof chartSettings.primaryLineThickness != 'undefined' ? chartSettings.primaryLineThickness : 2,
          comparable:                 searchMode === 'comparison',
          compareGraph:               {
            type:                       !chartSettings.primaryChartType || chartSettings.primaryChartType == 'candlestick' ? 'smoothedLine' : chartSettings.primaryChartType,
            fillAlphas:                 chartSettings.primaryLineColorAlpha || 0.15,
            lineThickness:              typeof chartSettings.primaryLineThickness != 'undefined' ? chartSettings.primaryLineThickness : 2,
            balloonText:                '[[title]]: <b>[[value]]</b>'
          },
          balloonFunction:              priceChartBalloonText,
          compareGraphBalloonFunction:  priceChartBalloonText,
          useDataSetColors:             false
        }],
        stockLegend: {
          enabled:                  typeof chartSettings.legendEnabled == 'undefined' ? true : chartSettings.legendEnabled,
          //position:                 chartSettings.legendPosition,
          color:                    chartSettings.color || '#383838',
          fontSize:                 chartSettings.fontSize || 14,
          backgroundColor:          chartSettings.backgroundColor || '#fff', // this is required for export to work as it doesn't take into account background set in CSS
          backgroundAlpha:          0,
          useGraphSettings:         true,
          equalWidths:              false,
          valueWidth:               150,
          valueText:                '[[value]] ',
          valueTextRegular:         '[[value]] ',
          valueTextComparing:       '[[percents.value]]%',
          periodValueText:          '[[value.close]]',
          periodValueTextRegular:   '[[value.close]]',
          periodValueTextComparing: '[[percents.value.close]]%'
        },
        valueAxes: [{
          position:       'right',
          color:          chartSettings.color || '#383838', // color of values
          gridColor:      chartSettings.gridColor || '#e0e0e0', //horizontal grid line color
          gridAlpha:      chartSettings.gridAlpha || 0.8,
          gridThickness:  typeof chartSettings.gridThickness != 'undefined' ? chartSettings.gridThickness : 1
        }]
      }, {
        title:              chartSettings.secondaryPanelTitle || 'Volume',
        percentHeight:      chartSettings.secondaryChartType != 'none' ? 30 : 0,
        precision:          0,
        stockGraphs: [{
          valueField:       'volume',
          type:             chartSettings.secondaryChartType || 'column',
          showBalloon:      true,
          lineColor:        chartSettings.secondaryLineColor || '#00842c',
          fillAlphas:       chartSettings.secondaryLineColorAlpha || 0.15,
          lineThickness:    typeof chartSettings.secondaryLineThickness != 'undefined' ? chartSettings.secondaryLineThickness : 1,
          balloonText:      '[[title]]: <b>' + '[[value]]</b>',
          useDataSetColors: false,
          comparable:       searchMode === 'comparison',
          periodValue:      'Sum',
          compareGraph:     {
            type:             chartSettings.secondaryChartType || 'column',
            fillAlphas:       chartSettings.secondaryLineColorAlpha || 0.15,
            lineThickness:    typeof chartSettings.secondaryLineThickness != 'undefined' ? chartSettings.secondaryLineThickness : 1,
            balloonText:      '[[title]]: <b>' + '[[value]]</b>'
          }
        }],
        stockLegend: {
          color:                    chartSettings.color || '#383838',
          valueText:                '[[value]]',
          valueTextRegular:         '[[value]]',
          valueTextComparing:       '[[percents.value]]%',
          periodValueText:          '[[value.close]]',
          periodValueTextRegular:   '[[value.close]]',
          periodValueTextComparing: '[[percents.value.close]]%'
        },
        valueAxes: [{
          position: 'right',
          color:          chartSettings.color || '#383838', // color of values
          gridColor:      chartSettings.gridColor || '#e0e0e0', //horizontal grid line color
          gridAlpha:      chartSettings.gridAlpha || 0.8,
          gridThickness:  typeof chartSettings.gridThickness != 'undefined' ? chartSettings.gridThickness : 1
        }]
      }],

      chartScrollbarSettings: {
        graph:                    'mainGraph',
        graphType:                'smoothedLine',
        enabled:                  typeof chartSettings.scrollbarEnabled == 'undefined' ? true : chartSettings.scrollbarEnabled,
        color:                    chartSettings.color || '#383838',
        backgroundColor:          chartSettings.scrollbarBackgroundColor || '#e8e8e8',
        backgroundAlpha:          1,
        selectedBackgroundColor:  chartSettings.scrollbarSelectedBackgroundColor || '#f7f7f7',
        selectedBackgroundAlpha:  1,
        graphFillColor:           chartSettings.scrollbarGraphFillColor || '#004c19',
        graphFillAlpha:           1,
        selectedGraphFillColor:   chartSettings.scrollbarSelectedGraphFillColor || '#00aa38',
        selectedGraphFillAlpha:   1,
        gridColor:                chartSettings.gridColor || '#e0e0e0',
        gridAlpha:                chartSettings.gridAlpha || 0.8,
        gridThickness:            typeof chartSettings.gridThickness != 'undefined' ? chartSettings.gridThickness : 1
      },

      chartCursorSettings: {
        enabled:                    typeof chartSettings.cursorEnabled == 'undefined' ? true : chartSettings.cursorEnabled,
        cursorColor:                chartSettings.cursorColor || '#ba0000',
        cursorAlpha:                chartSettings.cursorAlpha || 0.8,
        valueLineAlpha:             chartSettings.cursorAlpha || 0.8,
        valueBalloonsEnabled:       true,
        graphBulletSize:            1,
        valueLineBalloonEnabled:    true,
        valueLineEnabled:           true,
        categoryBalloonColor:       chartSettings.cursorColor || '#ba0000',
        categoryBalloonAlpha:       chartSettings.cursorAlpha || 0.8
      },

      periodSelector: {
        position: 'top',
        periodsText: '',
        inputFieldsEnabled: false, //disable dates input
        periods: []
      },

      dataSetSelector: {
        position: '' // leave empty to hide the native dataSet selection control
      },

      comparedDataSets: [],

      export: {
        enabled:  typeof chartSettings.exportEnabled == 'undefined' ? true : chartSettings.exportEnabled,
        position: 'top-right'
      },

      listeners: [{
        event: 'init',
        method: function () {
          // init compare select2 dropdown
          if (searchMode !== false)
            $classAssetAutocomplete.show().stockMarketChartsPlugin().initAssetAutocomplete();
        }
      }]
    };

    // additional settings for candlestick chart
    if (chartSettings.primaryChartType == 'candlestick') {
      chartOptions.panels[0].stockGraphs[0]['openField']        = 'open';
      chartOptions.panels[0].stockGraphs[0]['lowField']         = 'low';
      chartOptions.panels[0].stockGraphs[0]['highField']        = 'high';
      chartOptions.panels[0].stockGraphs[0]['closeField']       = 'value';
      chartOptions.panels[0].stockGraphs[0]['proCandlesticks']  = true;
      chartOptions.dataSets[0].fieldMappings.push({fromField: 'open', toField: 'open'});
      chartOptions.dataSets[0].fieldMappings.push({fromField: 'low', toField: 'low'});
      chartOptions.dataSets[0].fieldMappings.push({fromField: 'high', toField: 'high'});
    }

    // add predefined chart periods
    var periods = typeof chartSettings.periods != 'undefined' ? chartSettings.periods.split(',') : ['1W','1M','6M','1Y','ALL'];
    var defaultPeriod = chartSettings.defaultPeriod || '1M';
    for (var i = 0; i < periods.length; i++) {
      var periodName = periods[i];
      if (typeof supportedPeriods[periodName] != 'undefined') {
        chartOptions.periodSelector.periods[i] = supportedPeriods[periodName];
        // set default (selected) chart period
        if (periodName == defaultPeriod) {
          chartOptions.periodSelector.periods[i].selected = true;
        } else {
          chartOptions.periodSelector.periods[i].selected = false;
        }
      }
    }

    // initialize an empty chart (without data)
    chart = am.makeChart(containerId+'-chart', chartOptions);

    function formatLegendValue(graphDataItem, valueText) {
      return valueText;
    }

    if (searchMode !== false) {
      $classAssetAutocomplete.on('change', function () {
        var $select = $(this);
        var assets = $select.val();
        if (searchMode === 'search') {
          addComparison(assets);
        } else {
          // all selected assets removed
          if (!assets) {
            for (var i = 0; i < comparisonSelectSymbols.length; i++) {
              deleteComparison(comparisonSelectSymbols[i]);
            }
            comparisonSelectSymbols = [];
          } else {
            var removedAssets = subtract(comparisonSelectSymbols, assets);
            var addedAssets = subtract(assets, comparisonSelectSymbols);

            if (removedAssets.length)
              deleteComparison(removedAssets[0]);

            if (addedAssets.length)
              addComparison(addedAssets[0]);

            comparisonSelectSymbols = assets;
          }
        }
      });
    }

    if (comparisonSelectSymbols.length) {
      for (var i=0; i<comparisonSelectSymbols.length; i++)
        addComparison(comparisonSelectSymbols[i]);
    }

    function getAjaxUrl (symbol, range, interval) {
      return smcGlobals.ajaxUrl + '?action=' + smcGlobals.ajaxGetData + '&symbol=' + symbol + '&range=' + range + '&interval=' + interval;
    }

    function getDataSetTitle (symbol) {
      return smcGlobals.assetNamesOverrides && smcGlobals.assetNamesOverrides[symbol]
        ? smcGlobals.assetNamesOverrides[symbol]
        : symbol
    }

    /**
     * Add comparison to chart
     */
    function addComparison(symbol) {
      log('addComparison', symbol);
      if (typeof chart != 'undefined') {
        if (searchMode === 'search') {
          chart.dataSets[0].title = getDataSetTitle(symbol);
          chart.dataSets[0].dataLoader.url = getAjaxUrl(symbol, range, interval);
          chart.dataLoader.loadData();
        } else {
          chartSetLoadingState();
          // if asset is not added to comparison already (in which case the data would be already loaded)
          if ($.inArray(symbol, loadedComparisonSeries) == -1) {
            $.ajax({
              url: smcGlobals.ajaxUrl,
              method: 'post',
              dataType: 'json',
              data: {
                action: smcGlobals.ajaxGetData,
                symbol: symbol,
                range: range,
                interval: interval
              }
            }).done(function (response) {
              log('response', response);
              if (response.success) {
                loadedComparisonSeries.push(symbol);
                var dataSet = {
                  title: smcGlobals.assetNamesOverrides && smcGlobals.assetNamesOverrides[response.symbol] ? smcGlobals.assetNamesOverrides[response.symbol] : response.symbol,
                  assetSymbol: symbol,
                  compared: true,
                  fieldMappings: [{
                    fromField: 'value',
                    toField: 'value'
                  }, {
                    fromField: 'volume',
                    toField: 'volume'
                  }],
                  dataProvider: response.data,
                  categoryField: 'time'
                };
                chart.dataSets.push(dataSet);
                chart.comparedDataSets.push(dataSet);
                chart.validateData();
                chartRemoveLoadingState();
              } else {
                //deleteFromArray(loadedComparisonSeries, assetId);
                setTimeout(function () {
                  chartRemoveLoadingState();
                }, 3000);
              }
            });
            // If data was already loaded before just add it to comparison
          } else {
            for (var i = 0; i < chart.dataSets.length; i++) {
              if (chart.dataSets[i].assetSymbol == symbol) {
                chart.dataSets[i].compared = true;
              }
            }
            chart.validateData();
            chartRemoveLoadingState();
          }
        }
      }
    }

    /**
     * Delete comparison
     */
    function deleteComparison(assetId) {
      log('deleteComparison', assetId);
      // set compared property to false to hide the comparison, so it can be enabled again if the same comparison is added
      for (var i = 0; i < chart.dataSets.length; i++) {
        if (chart.dataSets[i].assetSymbol == assetId) {
          chart.dataSets[i].compared = false;
        }
      }
      chart.validateData();
    }

    function chartSetLoadingState() {
      //$chartPreloader.show();
    }

    function chartRemoveLoadingState() {
      //$chartPreloader.hide();
    }

    /**
     * Format balloon for price chart
     * @param item
     * @param graph
     * @returns {*}
     */
    function priceChartBalloonText(item, graph) {
      var result;
      if (graph.type == 'candlestick') {
        var open  = AmCharts.formatNumber(item.values.open,  {precision: -1, decimalSeparator: chartSettings.decimalSeparator || '.', thousandsSeparator: chartSettings.thousandsSeparator || ','}, chartSettings.precision || 2);
        var high  = AmCharts.formatNumber(item.values.high,  {precision: -1, decimalSeparator: chartSettings.decimalSeparator || '.', thousandsSeparator: chartSettings.thousandsSeparator || ','}, chartSettings.precision || 2);
        var low   = AmCharts.formatNumber(item.values.low,   {precision: -1, decimalSeparator: chartSettings.decimalSeparator || '.', thousandsSeparator: chartSettings.thousandsSeparator || ','}, chartSettings.precision || 2);
        var close = AmCharts.formatNumber(item.values.close, {precision: -1, decimalSeparator: chartSettings.decimalSeparator || '.', thousandsSeparator: chartSettings.thousandsSeparator || ','}, chartSettings.precision || 2);
        result = '<div class="amcharts-tooltip"><div>' + graph.title + '</div><table><tbody><tr><td>'+(smcGlobals.text.open||'Open')+':</td><td><b>'+open+'</b></td></tr><tr><td>'+(smcGlobals.text.high||'High')+':</td><td><b>'+high+'</b></td></tr><tr><td>'+(smcGlobals.text.low||'Low')+':</td><td><b>'+low+'</b></td></tr><tr><td>'+(smcGlobals.text.close||'Close')+':</td><td><b>'+close+'</b></td></tr></tbody></table></div>';
      } else {
        var value = AmCharts.formatNumber(item.values.value, {precision: -1, decimalSeparator: chartSettings.decimalSeparator || '.', thousandsSeparator: chartSettings.thousandsSeparator || ','}, chartSettings.precision || 2);
        result = '<div class="amcharts-tooltip">' + graph.title + ': <b>' + value + '</b></div>';
      }

      return result;
    }
  }

  // hide custom JQuery functions inside a namespace
  $.fn.stockMarketChartsPlugin = function() {
    var self = this;
    var $self = $(this);
    return {
      // asset search dropdown autocomplete
      initAssetAutocomplete: function() {
        return self.select2({
          allowClear: $self.attr('multiple') ? true : false,
          placeholder: typeof smcGlobals.text.select2 != 'undefined' ? smcGlobals.text.select2.placeholder : 'Symbol or asset name',
          containerCssClass: code + '-select2-container',
          dropdownCssClass:  code + '-select2-dropdown',
          ajax: {
            url: smcGlobals.ajaxUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
              return {
                action: smcGlobals.ajaxSymbolAutocomplete,
                q: params.term
              };
            },
            processResults: function (data, params) {
              params.page = params.page || 1;
              return {
                results: data
              };
            },
            cache: true
          },
          language: {
            noResults: function() {
              return typeof smcGlobals.text.select2 != 'undefined' ? smcGlobals.text.select2.search_not_found : 'No results found';
            },
            errorLoading: function() {
              return typeof smcGlobals.text.select2 != 'undefined' ? smcGlobals.text.select2.search_error : 'There was an error, please try again.';
            },
            inputTooShort: function() {
              return typeof smcGlobals.text.select2 != 'undefined' ? smcGlobals.text.select2.search_short : 'Enter at least 2 characters';
            },
            searching: function () {
              return typeof smcGlobals.text.select2 != 'undefined' ? smcGlobals.text.select2.searching : 'Searching...';
            }
          },
          escapeMarkup: function (markup) {
            return markup;
          },
          minimumInputLength: 1,
          templateResult: function (item) {
            if (item.loading) return item.name;

            return '<div class="smc-symbol-search-row">' +
              '  <span class="smc-symbol-search-symbol">' + item.id + '</span>' +
              '  <span class="smc-symbol-search-exch">' + item.class + ' - ' + item.exchange + '</span>' +
              '</div>' +
              '<div>' + item.name + '</div>';
          },
          templateSelection: function (item) {
            return item.id;
          }
        });
      }
    };
  };

  function log() {
    if (smcGlobals.debug) {
      console.log('SMC', arguments);
    }
  }

  /**
   * Subtract one array from another and return difference
   * subtract( [1,2,3,4,5,6], [3,4,5] ) => [1, 2, 6]
   * @param array
   * @param subtractedArray
   * @returns {Array}
   */
  function subtract(array, subtractedArray) {
    return subtractedArray ? array.filter(function(element) {return subtractedArray.indexOf(element) < 0;}) : [];
  }

  return {
    buildChart: buildChart,
    log: log
  };
})(jQuery, AmCharts);
