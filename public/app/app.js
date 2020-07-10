(function(){
    "use strict";

    angular.module('app', ['ngAnimate', 'ui.bootstrap', 'CountryBasedOwner'])

    .config(function($interpolateProvider, $httpProvider) {
        $interpolateProvider.startSymbol('<%');
        $interpolateProvider.endSymbol('%>');
    })

    .constant('BASE', {
        
        'API_URL': window.__env.API_URL,
        
        'ASSETS_URL': 'app/components/',

        'API_VERSION': 'api/v1/',
        
    })

    .directive("selectPicker", Select2)
    
    function Select2($parse, $timeout){
        return {
            restrict: 'A',
            priority: 1000,
            link: function (scope, element, attrs) {

              function refresh(newVal) {

                scope.$applyAsync(function () {

                  element.selectpicker();
                  
                  element.selectpicker('refresh'); 
                  
                });
              }

              attrs.$observe('spTheme', function (val) {
                $timeout(function () {
                  element.data('selectpicker').$button.removeClass(function (i, c) {
                    return (c.match(/(^|\s)?btn-\S+/g) || []).join(' ');
                  });
                  element.selectpicker('setStyle', val);
                });
              });

              $timeout(function () {
                element.selectpicker($parse(attrs.selectpicker)());
                element.selectpicker('refresh'); 
              });

              if (attrs.ngModel) {
                scope.$watch(attrs.ngModel, refresh, true);
              }

              if (attrs.ngDisabled) {
                scope.$watch(attrs.ngDisabled, refresh, true);
              }

              scope.$on('$destroy', function () {
                $timeout(function () {
                  element.selectpicker('destroy');
                });
              });

              if (attrs.obj) {
                scope.$watch(attrs.obj, refresh, true);
              }
              
            }
        };
    }

})();