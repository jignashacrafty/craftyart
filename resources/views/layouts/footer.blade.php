@include('layouts.masterscript')

<script src="{{ asset('assets/vendors/scripts/core.js') }}"></script>
<script src="{{ asset('assets/vendors/scripts/script.min.js') }}"></script>
<script>
    // Hide loader when page is fully loaded
    window.addEventListener('load', function () {
        document.getElementById('main_loading_screen').style.display = 'none';
    });
</script>
</body>

</html>