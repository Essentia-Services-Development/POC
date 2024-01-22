externalImporter.service(
        "extractorService",
        function ($http, $q) {
            return({
                getProducts: getProducts,
                importProduct: importProduct,
            });
            function getProducts(params) {
                var params = {
                    'action': 'ei-extractor-api',
                    'params': JSON.stringify(params),
                    '_ei_nonce': ei_params.nonce,
                };

                var request = $http({
                    method: "post",
                    url: ajaxurl,
                    data: params,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                    transformRequest: function (obj) {
                        var str = [];
                        for (var p in obj)
                            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                        return str.join("&");
                    },
                });
                return(request.then(handleSuccess, handleError2));
            }
            function importProduct(params) {
                
                var params = {
                    'action': 'ei-import-api',
                    'data': JSON.stringify(params),
                    '_ei_nonce': ei_params.nonce,
                };                
                
                var request = $http({
                    method: "post",
                    url: ajaxurl,
                    data: params,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                    transformRequest: function (obj) {
                        var str = [];
                        for (var p in obj)
                            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                        return str.join("&");
                    },                    
                });
                return(request.then(handleSuccess, handleError));
            }
            function handleError(response) {
                if (
                        !angular.isObject(response.data) ||
                        !response.data.log
                        ) {
                    return($q.reject({log: [{message: "An unknown error occurred. Please refresh the page and try again.", type: "error"}]}));
                }
                return($q.reject(response.data.log));
            }
            function handleSuccess(response) {
                return(response.data);
            }
            function handleError2(response) {
                return(response.data);
            }
        }
);
