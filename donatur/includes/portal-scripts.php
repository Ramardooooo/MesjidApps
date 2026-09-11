<script>

/*
|--------------------------------------------------------------------------
| ELEMENT
|--------------------------------------------------------------------------
*/

const appShell =
    document.getElementById(
        'appShell'
    );

const editModal =
    document.getElementById(
        'editModal'
    );

const notificationPanel =
    document.getElementById(
        'notificationPanel'
    );


/*
|--------------------------------------------------------------------------
| SIDEBAR
|--------------------------------------------------------------------------
*/

function openSidebar()
{
    appShell.classList.add(
        'mobile-sidebar-open'
    );
}


function closeSidebar()
{
    appShell.classList.remove(
        'mobile-sidebar-open'
    );
}


/*
|--------------------------------------------------------------------------
| EDIT MODAL
|--------------------------------------------------------------------------
*/

function openEditModal(focusPhoto = false)
{
    editModal.classList.remove(
        'hidden'
    );

    editModal.classList.add(
        'flex'
    );

    document.body.classList.add(
        'overflow-hidden'
    );

    if (focusPhoto) {

        setTimeout(
            function()
            {
                document
                    .getElementById(
                        'foto_profil'
                    )
                    ?.click();
            },
            150
        );

    } else {

        setTimeout(
            function()
            {
                document
                    .getElementById(
                        'nama_lengkap'
                    )
                    ?.focus();
            },
            100
        );
    }
}


function closeEditModal()
{
    editModal.classList.add(
        'hidden'
    );

    editModal.classList.remove(
        'flex'
    );

    document.body.classList.remove(
        'overflow-hidden'
    );
}


/*
|--------------------------------------------------------------------------
| PREVIEW FOTO
|--------------------------------------------------------------------------
*/

function previewPhoto(input)
{
    if (
        !input.files ||
        !input.files[0]
    ) {
        return;
    }

    const file =
        input.files[0];

    /*
    | Cek ukuran di browser
    */
    if (
        file.size >
        3 * 1024 * 1024
    ) {

        alert(
            'Ukuran foto maksimal 3MB.'
        );

        input.value = '';

        return;
    }


    /*
    | Preview
    */
    const reader =
        new FileReader();

    reader.onload =
        function(event)
        {

            const modalAvatar =
                document.getElementById(
                    'modalAvatar'
                );

            const heroAvatar =
                document.getElementById(
                    'heroAvatar'
                );


            /*
            | Modal
            */
            if (
                modalAvatar
            ) {

                if (
                    modalAvatar.tagName
                    .toLowerCase()
                    === 'img'
                ) {

                    modalAvatar.src =
                        event.target.result;

                } else {

                    const img =
                        document.createElement(
                            'img'
                        );

                    img.id =
                        'modalAvatar';

                    img.src =
                        event.target.result;

                    img.className =
                        'w-24 h-24 rounded-2xl object-cover border-2 border-antique-500/50';

                    modalAvatar.replaceWith(
                        img
                    );
                }
            }


            /*
            | Hero
            */
            if (
                heroAvatar
            ) {

                if (
                    heroAvatar.tagName
                    .toLowerCase()
                    === 'img'
                ) {

                    heroAvatar.src =
                        event.target.result;

                }
            }

        };


    reader.readAsDataURL(file);
}


/*
|--------------------------------------------------------------------------
| NOTIFICATION
|--------------------------------------------------------------------------
*/

function toggleNotification()
{
    notificationPanel.classList.toggle(
        'hidden'
    );
}


/*
|--------------------------------------------------------------------------
| CLICK OUTSIDE NOTIFICATION
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'click',
    function(event)
    {

        const button =
            event.target.closest(
                'button'
            );

        const panel =
            event.target.closest(
                '#notificationPanel'
            );


        if (
            !panel &&
            !button?.onclick
        ) {

            notificationPanel.classList.add(
                'hidden'
            );

        }

    }
);


/*
|--------------------------------------------------------------------------
| ESC
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'keydown',
    function(event)
    {

        if (
            event.key === 'Escape'
        ) {

            closeEditModal();

            notificationPanel.classList.add(
                'hidden'
            );

            closeSidebar();

        }

    }
);

</script>
