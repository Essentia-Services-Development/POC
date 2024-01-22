var externalImporter = angular.module('externalImporter', ['ui.bootstrap', 'ngSanitize', 'ngAnimate']);
externalImporter.controller('ExternalImporterController', function ($scope, extractorService, $timeout) {

    const MAX_PRODUCT_ARRAY_LENGTH = 1000;
    const MAX_LOG_ARRAY_LENGTH = 200;

    $scope.settings = {};
    $scope.products = [];
    $scope.log = [];
    $scope.stat = {};
    $scope.stat.success = $scope.stat.errors = $scope.stat.new = 0;
    $scope.importStat = {};
    $scope.importStat.success = $scope.importStat.errors = 0;
    $scope.listingProcessor = {};
    $scope.listingProcessor.params = {};
    $scope.listingProcessor.params.salt = rnd();
    $scope.listingProcessor.params.url = '';
    $scope.listingProcessor.params.max_count = 200;
    $scope.listingProcessor.params.automatic_pagination = true;
    $scope.productProcessor = {};
    $scope.productProcessor.params = {};
    $scope.productProcessor.params.salt = rnd();
    $scope.productProcessor.params.urls = [];
    $scope.loading = false;
    $scope.settings.timeout = 1;
    $scope.in_progress = false;
    $scope.in_waiting = false;
    $scope.importQueue = [];
    $scope.importParams = {};
    $scope.import_in_progress_count = 0;
    $scope.importSettings = {};
    $scope.importSettings.threads = 3;
    $scope.debug = '';

    $scope.$watchCollection('importQueue', function (newValue, oldValue) {
        if (!newValue.length)
            return;
        if (oldValue.length > newValue.length)
            return;
        $scope.maybeImport();
    });

    $scope.maybeImport = function () {
        if ($scope.import_in_progress_count >= $scope.importSettings.threads)
            return;
        var threads = $scope.importSettings.threads - $scope.import_in_progress_count;
        if (threads > $scope.importQueue.length)
            threads = $scope.importQueue.length;

        for (var i = 0; i < threads; i++) {
            var product_id = $scope.importQueue[0];
            $scope.importQueue.splice(0, 1);
            $scope.productImport(product_id);
        }
    };

    $scope.maybeCleanup = function () {
        if ($scope.import_in_progress_count.length || $scope.importQueue.length)
            return;
        for (const product of $scope.products) {
            if (product)
                return;
        }
        $scope.products = [];
        $scope.checkAll = false;
    };

    $scope.startImport = function (processor) {
        $scope.loading = true;

        var params = {[processor]: $scope[processor].params};
        loadRemoteData(params);
    };

    $scope.stopImport = function (processor) {
        $scope.loading = false;
    };

    $scope.restartImport = function (processor) {
        $scope[processor].params.salt = rnd();
        $scope.deleteAllProducts();
        $scope.debug = '';
        $scope.startImport(processor);
    };

    $scope.addToImportQueue = function (product_id) {
        if ($scope.importQueue.indexOf(product_id) > -1)
            return;
        $scope.products[product_id]._import_in_queue = true;
        $scope.importQueue.push(product_id);
        $scope.products[product_id]._selected = false;
    };

    $scope.deleteProduct = function (product_id) {
        var product = $scope.products[product_id];
        if (product && !product._import_in_queue && !product._import_status && !product._import_in_progress)
            delete $scope.products[product_id];
        $scope.maybeCleanup();
    };

    $scope.deleteAllProducts = function () {

        $scope.maybeCleanup();
        if (!$scope.products.length)
            return;

        $scope.products.forEach(function (product, product_id) {
            $scope.deleteProduct(product_id);
        });
    };

    $scope.productImport = function (product_id) {
        $scope.import_in_progress_count++;
        $scope.products[product_id]._import_in_queue = false;
        $scope.products[product_id]._import_in_progress = true;

        var product = $scope.products[product_id];
        product._index = product_id;
        var params = {'product': product, 'params': $scope.importParams};
        loadImportResponse(params);
    };

    $scope.selectedCount = function () {
        var count = 0;
        angular.forEach($scope.products, function (product, key) {
            if (product._selected)
                count++;
        });
        return count;
    };

    $scope.toggleSeleted = function () {
        angular.forEach($scope.products, function (product, key) {
            if (product && !product._import_in_queue && !product._import_status && !product._import_in_progress) {
                product._selected = $scope.checkAll;
            }
        });
    };

    $scope.importSelected = function () {
        angular.forEach($scope.products, function (product, key) {
            if (product && product._selected) {
                $scope.addToImportQueue(key);
                product._selected = false;
            }
        });
        $scope.checkAll = false;
    };

    $scope.importAll = function () {
        angular.forEach($scope.products, function (product, key) {
            if (product && !product._import_status && !product._import_in_queue && !product._import_in_progress) {
                $scope.addToImportQueue(key);
            }
        });
    };

    $scope.initAutomaticImport = function () {
        if ($scope.automaticImport)
            $scope.importAll();
    };

    $scope.rnd = function () {
        return rnd();
    };

    function rnd() {
        return Date.now();
    }

    function getTimeout()
    {
        if ($scope.settings.timeout >= 0)
            return $scope.settings.timeout * 1000;

        var min = 1;
        var min = 10;
        if ($scope.settings.timeout == -1)
            max = 5;
        if ($scope.settings.timeout == -2)
            max = 10;

        return (Math.floor(Math.random() * (max - min + 1)) + min) * 1000;
    }

    function isDublicateProduct(newProduct)
    {
        for (const product of $scope.products) {

            if (product && newProduct.link == product.link)
                return true;
        }
        return false;
    }

    function applyRemoteData(newData) {

        if (newData.products)
        {
            for (const newProduct of newData.products) {
                if (!isDublicateProduct(newProduct))
                    $scope.products.push(newProduct);
            }

            var defined_size = $scope.products.filter(function (value) {
                return value !== undefined;
            }).length;
            var undefined_size = $scope.products.length - defined_size;
            $scope.products.splice(MAX_PRODUCT_ARRAY_LENGTH + undefined_size);
        }

        if (newData.log)
        {
            newData.log.reverse();
            $scope.log = newData.log.concat($scope.log);
            $scope.log.splice(MAX_LOG_ARRAY_LENGTH);
        }

        if (newData.stat)
            $scope.stat = newData.stat;

        if (newData.debug)
            $scope.debug = newData.debug;

        if ($scope.automaticImport)
            $scope.importAll();
    }

    function loadRemoteData(params) {
        $scope.in_progress = true;
        $scope.in_waiting = false;
        extractorService.getProducts(params)
                .then(
                        function (data) {
                            applyRemoteData(data);
                            if ($scope.loading && data.cmd == 'next') {
                                $scope.in_progress = false;
                                $scope.in_waiting = true;
                                $timeout(function () {
                                    loadRemoteData(params)
                                }, getTimeout());
                            } else {
                                $scope.loading = false;
                                $scope.in_progress = false;
                                $scope.in_waiting = false;
                            }
                        }

                ).catch(function (data) {
            $scope.loading = false;
            $scope.in_progress = false;
            $scope.in_waiting = false;
            applyRemoteData(data);
        });
    }

    function productImportHandler(data) {
        $scope.import_in_progress_count--;
        var product_id = data.index;
        $scope.products[product_id]._import_in_progress = false;
        $scope.products[product_id]._import_status = data.status;

        var t = 1000;
        if (data.status === 'success')
            $scope.importStat.success++;

        if (data.status === 'error') {
            $scope.products[product_id]._import_message = 'Error: ' + data.message;
            $scope.importStat.errors++;
            t = 3000;
        }

        $scope.maybeImport();
        $timeout(function () {
            delete $scope.products[product_id];
            $scope.maybeCleanup();
        }, t);
    }

    function loadImportResponse(params) {
        extractorService.importProduct(params)
                .then(
                        function (data) {
                            productImportHandler(data);
                        }
                ).catch(function (data) {
            productImportHandler(data);
        });
    }
});

externalImporter.directive('onEnter', function () {
    var linkFn = function (scope, element, attrs) {
        element.on('keypress', function (event) {
            if (event.which === 13) {
                scope.$apply(function () {
                    scope.$eval(attrs.onEnter);
                });
                event.preventDefault();
            }
        });
    };
    return {
        link: linkFn
    };
});

externalImporter.directive('imageloaded', [
    function () {
        'use strict';
        return {
            restrict: 'A',
            link: function (scope, element, attrs) {
                var cssClass = attrs.loadedclass;

                element.on('load', function (e) {
                    angular.element(element).addClass(cssClass);
                });
            }
        }
    }
]);

externalImporter.directive('repeatDone', [function () {
        return {
            restrict: 'A',
            link: function (scope, element, iAttrs) {
                var parentScope = element.parent().scope();
                if (scope.$last) {
                    parentScope.$last = true;
                }
            }
        };
    }]);

externalImporter.directive('ngConfirmClick', function () {
    return {
        priority: -1,
        restrict: 'A',
        link: function (scope, element, attrs) {
            element.on('click', function (e) {
                var message = attrs.ngConfirmClick;
                if (message && !confirm(message)) {
                    e.stopImmediatePropagation();
                    e.preventDefault();
                }
            });
        }
    };
});

externalImporter.directive('selectOnClick', function () {
    return {
        restrict: 'A',
        link: function (scope, element, attrs) {
            element.on('click', function () {
                this.select();
            });
        }
    };
});

externalImporter.directive('convertToNumber', function () {
    return {
        require: 'ngModel',
        link: function (scope, element, attrs, ngModel) {
            ngModel.$parsers.push(function (val) {
                return val != null ? parseInt(val, 10) : null;
            });
            ngModel.$formatters.push(function (val) {
                return val != null ? '' + val : null;
            });
        }
    };
});