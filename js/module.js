registerController('radiusController', ['$api', '$scope', '$rootScope', '$interval', '$timeout', function($api, $scope, $rootScope, $interval, $timeout) {
    $scope.title = "Loading...";
    $scope.version = "Loading...";

  $scope.refreshInfo = (function() {
        $api.request({
            module: 'RadiusPineapple',
            action: "refreshInfo"
        }, function(response) {
                        $scope.title = response.title;
                        $scope.version = "v"+response.version;
        })
    });

        $scope.refreshInfo();

}]);

registerController('radiusControlsController', ['$api', '$scope', '$rootScope', '$interval', '$timeout', function($api, $scope, $rootScope, $interval, $timeout) {
    $scope.status = "Loading...";
    $scope.statusLabel = "default";
    $scope.starting = false;

    $scope.install = "Loading...";
    $scope.installLabel = "default";
    $scope.processing = false;

    $scope.device = '';
    $scope.sdAvailable = false;

    $rootScope.status = {
        installed : false,
        refreshOutput : false,
        refreshHistory : false
    };

  $scope.refreshStatus = (function() {
        $api.request({
            module: "RadiusPineapple",
            action: "refreshStatus"
        }, function(response) {
            $scope.status = response.status;
            $scope.statusLabel = response.statusLabel;

            $rootScope.status.installed = response.installed;
            $scope.device = response.device;
            $scope.sdAvailable = response.sdAvailable;
            if(response.processing) $scope.processing = true;
            $scope.install = response.install;
            $scope.installLabel = response.installLabel;
        })
    });

  $scope.togglenmap = (function() {
        if($scope.status != "Stop")
            $scope.status = "Starting...";
        else
            $scope.status = "Stopping...";

        $scope.statusLabel = "warning";
        $scope.starting = true;

        $api.request({
                module: 'RadiusPineapple',
                action: 'toggleRadius',
                command: $rootScope.command
            }, function(response) {
                $timeout(function(){
                            $rootScope.status.refreshOutput = true;
                            $rootScope.status.refreshHistory = false;

                            $scope.starting = false;
                            $scope.refreshStatus();

                            $scope.scanInterval = $interval(function(){
                                    $api.request({
                                            module: 'RadiusPineapple',
                                            action: 'scanStatus'
                                    }, function(response) {
                                            if (response.success === true){
                                                    $interval.cancel($scope.scanInterval);
                                                    $rootScope.status.refreshOutput = false;
                                                    $rootScope.status.refreshHistory = true;
                                            }
                                            $scope.refreshStatus();
                                    });
                            }, 5000);

                }, 2000);
            })
    });

  $scope.handleDependencies = (function(param) {
    if(!$rootScope.status.installed)
            $scope.install = "Installing...";
        else
            $scope.install = "Removing...";

        $api.request({
            module: 'nmap',
            action: 'handleDependencies',
                        destination: param
        }, function(response){
            if (response.success === true) {
                $scope.installLabel = "warning";
                $scope.processing = true;

                $scope.handleDependenciesInterval = $interval(function(){
                    $api.request({
                        module: 'nmap',
                        action: 'handleDependenciesStatus'
                    }, function(response) {
                        if (response.success === true){
                            $scope.processing = false;
                            $interval.cancel($scope.handleDependenciesInterval);
                            $scope.refreshStatus();
                        }
                    });
                }, 5000);
            }
        });
    });

    $scope.refreshStatus();
}]);

registerController('radiusOutputController', ['$api', '$scope', '$rootScope', '$interval', function($api, $scope, $rootScope, $interval) {
  $scope.output = 'Loading...';
    $scope.filter = '';

    $scope.refreshLabelON = "default";
    $scope.refreshLabelOFF = "danger";

  $scope.refreshOutput = (function() {
        $api.request({
            module: "RadiusPineapple",
            action: "refreshOutput"
        }, function(response) {
            $scope.output = response;
        })
    });

  $scope.toggleAutoRefresh = (function() {
    if($scope.autoRefreshInterval)
        {
            $interval.cancel($scope.autoRefreshInterval);
            $scope.autoRefreshInterval = null;
            $scope.refreshLabelON = "default";
            $scope.refreshLabelOFF = "danger";
        }
        else
        {
            $scope.refreshLabelON = "success";
            $scope.refreshLabelOFF = "default";

            $scope.autoRefreshInterval = $interval(function(){
                $scope.refreshOutput();
            }, 5000);
        }
    });

    $scope.refreshOutput();

        $rootScope.$watch('status.refreshOutput', function(param) {
            if(param) {
                $scope.refreshOutput();
            }
        });

}]);