(function(){
    "use strict";

    angular.module('CountryBasedOwner', ['ui.bootstrap', 'ngAnimate'])

    .factory('UserInfsRepository', UserInfsRepository)

    .factory('CountryOwnerGroupRepository', CountryOwnerGroupRepository)

    .factory('OwnerRepository', OwnerRepository)

    .factory('CountryBasedOwnerRepository', CountryBasedOwnerRepository)

    .factory('FallbackOwnerRepository', FallbackOwnerRepository)

    .directive('countryBasedOwner', CountryBasedOwner)

    function CountryBasedOwner(BASE) {
        return {
            restrict: 'EA',
            scope: {
                defaultInfs: '@'
            },
            controller: CountryBasedOwnerController,
            templateUrl: BASE.API_URL + BASE.ASSETS_URL + 'country_based_owner/templates/index.html',
            link: function (scope, iElement, iAttrs) {

            }
        };
    }

    function CountryBasedOwnerController(BASE, $scope, $timeout, $uibModal, UserInfsRepository, CountryOwnerGroupRepository, OwnerRepository, CountryBasedOwnerRepository, $filter, FallbackOwnerRepository) {
        
        $scope.isLoaded = false;
        $scope.isOwnerLoaded = false;
        $scope.isFormSubmitting = false;
        $scope.isEditing = false;

        $scope.isFallbackOwnerListLoaded = false;
        $scope.isFallbackOwnerFormSubmitting = false;
        $scope.isFallbackOwnerEditing = false;

        $scope.fallback_owners = [];
        $scope.user_infs_account_id = $('.infusaccount').val();
        $scope.user_infs_accounts = [];
        $scope.country_owners = [];
        $scope.owners = [];
        $scope.countries = [];
        $scope.fallback_owner = [];

        $scope.fallback = {
            fallback_owner_id: 0,
            infs_account_id: 0,
            owner_name: '',
        };

        $scope.form = {
            owner_id: 0,
            country_id: 0,
            infs_account_id: 0,
            owner_name: '',
        }

        $(window).load(function() {
            $scope.user_infs_account_id = $('.infusaccount').val();
            $scope.loadCountryOwnerGroups();
            $scope.loadFallbackOwner();
        });
            
        
        $scope.loadInfsAccount = function(callback = false){

            UserInfsRepository.get().then(function(response){

                $scope.user_infs_accounts = response.data;
                $scope.isLoaded = true;

                $(".allowner").show();
                $('.loader').removeClass('fa-spinner fa-spin');
                $('#infsBtn').prop('disabled',false);

                $('.infusaccount option:last-child').attr('selected', 'selected');
                
                $scope.loadCountryOwnerGroups()
                
            });

        }

        $scope.addNew = function(){
            $scope.isEditing = false;
            $('#add-new').show();
            loadOwners();
        }

        $scope.showFallbackForm = function(){
            $scope.isFallbackOwnerListLoaded = false;
            $('#add-fallback').show();
            loadFallbackOwnerLists();
        }

        $scope.saveNewGroup = function(){

            $scope.isFormSubmitting = true;

            let owner_name = $filter('filter')($scope.owners, {'Id': $scope.form.owner_id}, true);

            $scope.form.owner_name = owner_name;

            CountryBasedOwnerRepository.save($scope.form).then(function(response){

                Swal.fire({
                  type: 'success',
                  title: 'Your work has been saved',
                  showConfirmButton: false,
                  timer: 1500
                })

                $scope.loadCountryOwnerGroups();

                $scope.isFormSubmitting = false;

                $timeout(function(){
                    $('#add-new').hide();
                    reset();
                }, 2000);

            });

        }

        $scope.saveFallbackOwner = function(){


            $scope.isFallbackOwnerFormSubmitting = true;

            let owner_name = $filter('filter')($scope.fallback_owners, {'Id': $scope.fallback.fallback_owner_id}, true);

            $scope.fallback.owner_name = owner_name;

            FallbackOwnerRepository.save($scope.fallback).then(function(response){

                Swal.fire({
                  type: 'success',
                  title: 'Success',
                  text: 'Your fallback owner has been saved',
                  showConfirmButton: false,
                  timer: 1500
                })

                $scope.loadFallbackOwner();

                $scope.isFallbackOwnerFormSubmitting = false;

                $timeout(function(){
                    $('#add-fallback').hide();
                    resetFallbackForm();
                }, 2000);

            }, function(response){

                let response_error = response.data.error.message;

                Swal.fire({
                  type: 'danger',
                  title: 'Failed',
                  text: response_error,
                  showConfirmButton: true
                })

                $scope.isFallbackOwnerFormSubmitting = false;

            });
        }

        $scope.editOwner = function(owner_obj){

            $scope.isEditing = true;
            $('#add-new').show();

            loadOwners(function(){
                let country_ids = [];
                angular.forEach(owner_obj.country_owner_groups, function(val, index){
                    country_ids.push(val['infs_country_id']);
                })

                $scope.form = {
                    owner_id: owner_obj.infs_person_id,
                    country_id: country_ids,
                    infs_account_id: $scope.user_infs_account_id,
                    owner_name: owner_obj.owner_name,
                    id: owner_obj.id,
                }

            });
            
        }

        $scope.saveEditedGroup = function(){

            $scope.isFormSubmitting = true;

            let owner_name = $filter('filter')($scope.owners, {'Id': $scope.form.owner_id}, true);

            $scope.form.owner_name = owner_name;

            CountryBasedOwnerRepository.update($scope.form.id, $scope.form).then(function(response){

                Swal.fire({
                  type: 'success',
                  title: 'Your work has been saved',
                  showConfirmButton: false,
                  timer: 1500
                })

                $scope.loadCountryOwnerGroups();

                $scope.isFormSubmitting = false;

                $timeout(function(){
                    $('#add-new').hide();
                    reset();
                    $scope.isEditing = false;
                }, 2000);

            });

        }

        $scope.saveEditedFallbackOwner = function(){

            $scope.isFallbackOwnerFormSubmitting = true;

            let owner_name = $filter('filter')($scope.fallback_owners, {'Id': $scope.fallback.fallback_owner_id}, true);

            $scope.fallback.owner_name = owner_name;

            FallbackOwnerRepository.update($scope.fallback.infs_account_id, $scope.fallback).then(function(response){

                Swal.fire({
                  type: 'success',
                  title: 'Your fallback owner has been updated',
                  showConfirmButton: false,
                  timer: 1500
                })

                $scope.loadFallbackOwner();

                $scope.isFallbackOwnerFormSubmitting = false;

                $timeout(function(){
                    $('#add-fallback').hide();
                    resetFallbackForm();
                    $scope.isFallbackOwnerEditing = false;
                }, 2000);

            });

        }

        $scope.deleteOwner = function(owner_id){

            Swal.fire({
              title: 'Are you sure?',
              text: "You won't be able to revert this!",
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
              if (result.value) {

                OwnerRepository.destroy(owner_id).then(function(response){

                    let response_data = response.data;

                    Swal.fire(
                      'Deleted!',
                      response_data.success.message,
                      'success'
                    );

                    $scope.loadCountryOwnerGroups();
                })

              }

            })

        }

        $scope.cancelEditing = function(){
            $('#add-new').hide();
            reset();
            $scope.isEditing = false;
        }

        $scope.cancelEditingFallback = function(){
            $('#add-fallback').hide();
            resetFallbackForm();
            $scope.isFallbackOwnerEditing = false;
        }

        $scope.showEditFallbackForm = function(){

            $scope.isFallbackOwnerEditing = true;
            $('#add-fallback').show();
            $scope.fallback.fallback_owner_id = $scope.fallback_owner[0].fallback_owner_id;
            loadFallbackOwnerLists();

        }

        $scope.loadCountryOwnerGroups = function() {

            let params = {
                    'accountID': $scope.user_infs_account_id,
                    '_token': $('#csrf_token').val()
                };

            $scope.form.infs_account_id = $scope.user_infs_account_id;

            CountryOwnerGroupRepository.getByUserInfsAccountId(params).then(function(response){
                let response_data = response.data.success.data;
                $scope.country_owners = response_data;
            });

        }

        $scope.loadFallbackOwner = function(){

            let params = {
                'infs_account_id': $scope.user_infs_account_id,
            };

            FallbackOwnerRepository.show($scope.user_infs_account_id, params).then(function(response){
                let response_data = response.data.success.data;
                $scope.fallback_owner = response_data;
            });

        }

        function loadOwners(callback = false) {

            $scope.isOwnerLoaded = false;

            let params = {
                'accountID': $scope.user_infs_account_id
            };

            OwnerRepository.get(params).then(function(response){
                let response_data = response.data.success.data;
                $scope.owners = response_data.owners;
                $scope.countries = response_data.countries;
                $scope.fallback_owners = response_data.owners;

                $scope.isOwnerLoaded = true;

                $('.country').select2();

                if (callback) {
                    callback();
                }
            });
        }

        function loadFallbackOwnerLists(callback = false) {

            $scope.isFallbackOwnerListLoaded = false;

            let params = {
                'accountID': $scope.user_infs_account_id
            };

            $scope.fallback.infs_account_id = $scope.user_infs_account_id;

            OwnerRepository.get(params).then(function(response){

                let response_data = response.data.success.data;

                $scope.fallback_owners = response_data.owners;

                $scope.isFallbackOwnerListLoaded = true;
                

                if (callback) {
                    callback();
                }

            });
        }

        function reset() {
            $scope.form = {
                owner_id: 0,
                country_id: 0,
                infs_account_id: 0,
                owner_name: '',
            }
        }

        function resetFallbackForm() {
            $scope.fallback = {
                fallback_owner_id: 0,
                infs_account_id: 0,
                owner_name: '',
            };
        }

    }

    function UserInfsRepository(BASE, $http) {
        
        let url = BASE.API_URL + BASE.API_VERSION + 'user/infusionsoft-accounts';

        let repo = {};

        repo.get = function() {
            return $http.get(url);
        }

        repo.store = function(params) {
            return $http.post(url, params);
        }

        return repo;

    }

    function CountryOwnerGroupRepository(BASE, $http) {
        
        let url = BASE.API_URL + BASE.API_VERSION + 'country-owner-groups';

        let repo = {};

        repo.get = function() {
            return $http.get(url);
        }

        repo.getByCountryOwnerId = function(country_owner_id) {
            return $http.get(url + '/by-country-owner-id/' + country_owner_id);
        }

        repo.getByUserInfsAccountId = function(params){
            return $http.post(url + '/get/by-user-infusionsoft-account-id', params);
        }

        return repo;
    }

    function OwnerRepository(BASE, $http) {
        
        let url = BASE.API_URL + BASE.API_VERSION + 'country-owners';

        let repo = {};

        repo.get = function(params) {
            return $http.get(url, {params});
        }

        repo.destroy = function(id){
            return $http.delete(url + '/' + id)
        }

        return repo;

    }

    function CountryBasedOwnerRepository(BASE, $http) {
        
        let url = BASE.API_URL + BASE.API_VERSION + 'country-based-owner';

        let repo = {};

        repo.save = function(params) {
            return $http.post(url, params);
        }

        repo.update = function(id, params){
            return $http.patch(url + '/' + id, params);
        }

        return repo;
    }

    function FallbackOwnerRepository(BASE, $http) {

        let url = BASE.API_URL + BASE.API_VERSION + 'fallback-owner';

        let repo = {};

        repo.show = function(id, params) {
            return $http.get(url + '/' + id, {params});
        }

        repo.save = function(params) {
            return $http.post(url, params);
        }

        repo.update = function(id, params){
            return $http.patch(url + '/' + id, params);
        }

        return repo;
    }

})();