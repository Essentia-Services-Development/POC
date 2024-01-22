contentEgg.filter('normalizeFlickr', function () {
    return function (input) {
        var pattern = RegExp(/_\w{1}\.jpg$/);
        return input.replace(pattern, '_m.jpg');
    }
}); 