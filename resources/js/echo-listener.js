// Echo channel listener
document.addEventListener('DOMContentLoaded', function() {
    if (window.Echo) {
        console.log('Echo is initialized, setting up channel listener');
        
        window.Echo.channel('updates-data')
            .listen('.data.updated', function(e) {
                console.log('Received update:', e);

                if (window.Swal) {
                    Swal.fire({
                        title: 'Notifikasi',
                        text: e.notification.message,
                        icon: e.notification.type,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    console.error('SweetAlert2 (Swal) is not available');
                }
            });
    } else {
        console.error('Echo is not initialized');
    }
});