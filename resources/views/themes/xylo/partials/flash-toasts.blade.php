@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.StoreNotify) {
                StoreNotify.success(@json(session('success')));
            }
        });
    </script>
@endif
@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.StoreNotify) {
                StoreNotify.error(@json(session('error')));
            }
        });
    </script>
@endif
@if (session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.StoreNotify) {
                StoreNotify.warning(@json(session('warning')));
            }
        });
    </script>
@endif
@if (session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.StoreNotify) {
                StoreNotify.info(@json(session('info')));
            }
        });
    </script>
@endif
