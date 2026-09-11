
    </div>

</div>



<!-- =============================================================
     MODAL EDIT PROFIL
============================================================== -->

<div
    id="editModal"

    class="
        hidden

        fixed
        inset-0

        z-[100]

        items-center
        justify-center

        p-4
    "
>

    <div
        class="
            absolute
            inset-0

            modal-backdrop
        "

        onclick="closeEditModal()"
    ></div>



    <div
        class="
            relative

            w-full
            max-w-lg

            bg-white

            rounded-2xl

            border
            border-antique-200

            shadow-luxury

            overflow-hidden
        "
    >


        <!-- HEADER -->

        <div
            class="
                px-5
                py-4

                bg-cypress-900

                text-white

                flex
                items-center
                justify-between
            "
        >

            <div>

                <h3
                    class="
                        text-sm

                        font-bold

                        text-antique-500
                    "
                >
                    Edit Profil
                </h3>


                <p
                    class="
                        text-[10px]

                        text-white/70

                        mt-1
                    "
                >
                    Perbarui informasi akun donatur.
                </p>

            </div>


            <button
                type="button"

                onclick="closeEditModal()"

                class="
                    w-8
                    h-8

                    rounded-lg

                    bg-white/5

                    text-white/70

                    hover:text-white
                "
            >

                <i
                    class="
                        fa-solid
                        fa-xmark
                    "
                ></i>

            </button>

        </div>



        <!-- FORM -->

        <form
            method="POST"

            action="portal-donatur.php"

            enctype="multipart/form-data"

            class="
                p-5

                space-y-4
            "
        >

            <input
                type="hidden"

                name="aksi"

                value="update_profil"
            >


            <!-- FOTO -->

            <div
                class="
                    flex
                    flex-col
                    items-center

                    pb-3
                "
            >

                <div
                    class="
                        relative
                    "
                >

                    <?php if ($avatarUrl): ?>

                        <img
                            id="modalAvatar"

                            src="<?= e($avatarUrl) ?>"

                            class="
                                w-24
                                h-24

                                rounded-2xl

                                object-cover

                                border-2
                                border-antique-500/50
                            "
                        >

                    <?php else: ?>

                        <div
                            id="modalAvatar"

                            class="
                                w-24
                                h-24

                                rounded-2xl

                                bg-cypress-800

                                border-2
                                border-antique-500/50

                                flex
                                items-center
                                justify-center

                                text-antique-300

                                text-xl
                                font-bold
                            "
                        >
                            <?= e($initials) ?>
                        </div>

                    <?php endif; ?>

                </div>


                <label
                    for="foto_profil"

                    class="
                        mt-3

                        cursor-pointer

                        px-4
                        py-2

                        rounded-xl

                        bg-antique-50

                        border
                        border-antique-300

                        text-antique-700

                        text-xs

                        font-bold

                        hover:bg-antique-100

                        transition
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-camera

                            mr-1
                        "
                    ></i>

                    Pilih Foto

                </label>


                <input
                    id="foto_profil"

                    type="file"

                    name="foto_profil"

                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"

                    onchange="previewPhoto(this)"
                >


                <p
                    class="
                        text-[10px]

                        text-warm-800/45

                        mt-2
                    "
                >
                    JPG, PNG, atau WEBP · Maksimal 3MB
                </p>

            </div>



            <!-- NAMA -->

            <div>

                <label
                    for="nama_lengkap"

                    class="
                        block

                        text-[11px]

                        font-bold

                        text-warm-800

                        mb-1.5
                    "
                >
                    Nama Lengkap
                </label>


                <input
                    id="nama_lengkap"

                    type="text"

                    name="nama_lengkap"

                    value="<?= e($namaDonatur) ?>"

                    required

                    class="
                        w-full

                        px-3.5
                        py-3

                        rounded-xl

                        bg-warm-50

                        border
                        border-antique-200

                        text-xs

                        outline-none

                        focus:border-antique-500

                        focus:ring-2
                        focus:ring-antique-500/20
                    "
                >

            </div>



            <!-- EMAIL -->

            <div>

                <label
                    class="
                        block

                        text-[11px]

                        font-bold

                        text-warm-800

                        mb-1.5
                    "
                >
                    Email
                </label>


                <input
                    type="email"

                    value="<?= e($emailDonatur) ?>"

                    disabled

                    class="
                        w-full

                        px-3.5
                        py-3

                        rounded-xl

                        bg-stone-100

                        border
                        border-stone-200

                        text-xs

                        text-warm-800/50

                        cursor-not-allowed
                    "
                >

            </div>



            <!-- WHATSAPP -->

            <div>

                <label
                    for="no_hp"

                    class="
                        block

                        text-[11px]

                        font-bold

                        text-warm-800

                        mb-1.5
                    "
                >
                    Nomor WhatsApp
                </label>


                <input
                    id="no_hp"

                    type="text"

                    name="no_hp"

                    value="<?= e($noHpDonatur) ?>"

                    placeholder="08xxxxxxxxxx"

                    class="
                        w-full

                        px-3.5
                        py-3

                        rounded-xl

                        bg-warm-50

                        border
                        border-antique-200

                        text-xs

                        outline-none

                        focus:border-antique-500

                        focus:ring-2
                        focus:ring-antique-500/20
                    "
                >

            </div>



            <!-- PASSWORD -->

            <div>

                <label
                    for="password_baru"

                    class="
                        block

                        text-[11px]

                        font-bold

                        text-warm-800

                        mb-1.5
                    "
                >
                    Kata Sandi Baru

                    <span
                        class="
                            font-normal

                            text-warm-800/45
                        "
                    >
                        (opsional)
                    </span>

                </label>


                <input
                    id="password_baru"

                    type="password"

                    name="password_baru"

                    placeholder="Minimal 6 karakter"

                    class="
                        w-full

                        px-3.5
                        py-3

                        rounded-xl

                        bg-warm-50

                        border
                        border-antique-200

                        text-xs

                        outline-none

                        focus:border-antique-500

                        focus:ring-2
                        focus:ring-antique-500/20
                    "
                >

            </div>



            <!-- BUTTON -->

            <div
                class="
                    flex
                    justify-end

                    gap-2

                    pt-2
                "
            >

                <button
                    type="button"

                    onclick="closeEditModal()"

                    class="
                        px-4
                        py-2.5

                        rounded-xl

                        border
                        border-antique-200

                        text-xs

                        font-semibold

                        text-warm-800

                        hover:bg-warm-50
                    "
                >
                    Batal
                </button>


                <button
                    type="submit"

                    class="
                        px-5
                        py-2.5

                        rounded-xl

                        bg-[#d9ad2f]

                        text-[#142a1f]

                        text-xs

                        font-bold

                        hover:brightness-105
                    "
                >

                    <i
                        class="
                            fa-solid
                            fa-check

                            mr-1
                        "
                    ></i>

                    Simpan Perubahan

                </button>

            </div>

        </form>

    </div>

</div>



<!-- =============================================================
     JAVASCRIPT
============================================================== -->

