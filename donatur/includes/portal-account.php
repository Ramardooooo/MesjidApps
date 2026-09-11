            <section
                id="informasi-akun"

                class="
                    mt-5
                    sm:mt-6

                    islamic-card

                    rounded-[22px]

                    border
                    border-antique-500/25

                    shadow-soft

                    overflow-hidden
                "
            >


                <!-- HEADER -->

                <div
                    class="
                        px-5
                        sm:px-6

                        py-5

                        border-b
                        border-antique-500/25

                        flex
                        items-center
                        justify-between

                        gap-4
                    "
                >

                    <div>

                        <h2
                            class="
                                text-sm
                                sm:text-base

                                font-bold

                                text-antique-500
                            "
                        >
                            Informasi Pribadi
                        </h2>


                        <p
                            class="
                                text-[11px]

                                text-white/80

                                mt-1
                            "
                        >
                            Kelola informasi pribadi kamu.
                        </p>

                    </div>


                    <button
                        type="button"

                        onclick="openEditModal(false)"

                        class="
                            shrink-0

                            px-4
                            sm:px-5

                            py-2.5

                            rounded-xl

                            bg-[#d9ad2f]

                            text-[#142a1f]

                            text-[11px]
                            sm:text-xs

                            font-bold

                            inline-flex
                            items-center
                            gap-2

                            hover:brightness-105

                            transition
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-pen

                                text-[10px]
                            "
                        ></i>

                        Edit Profil

                    </button>

                </div>



                <!-- BODY -->

                <div
                    class="
                        p-5
                        sm:p-6
                    "
                >

                    <div
                        class="
                            grid
                            grid-cols-1
                            lg:grid-cols-2

                            gap-5
                        "
                    >


                        <!-- NAMA -->

                        <div>

                            <label
                                class="
                                    block

                                    text-[11px]

                                    font-bold

                                    text-antique-500

                                    mb-2
                                "
                            >
                                Nama Lengkap
                            </label>


                            <div
                                class="
                                    min-h-[47px]

                                    rounded-xl

                                    px-3.5

                                    bg-white

                                    border
                                    border-antique-200

                                    flex
                                    items-center
                                    gap-3

                                    text-xs
                                    text-warm-800
                                "
                            >

                                <i
                                    class="
                                        fa-regular
                                        fa-user

                                        text-antique-500

                                        w-4
                                    "
                                ></i>

                                <?= e($namaDonatur) ?>

                            </div>

                        </div>



                        <!-- EMAIL -->

                        <div>

                            <label
                                class="
                                    block

                                    text-[11px]

                                    font-bold

                                    text-antique-500

                                    mb-2
                                "
                            >
                                Email
                            </label>


                            <div
                                class="
                                    min-h-[47px]

                                    rounded-xl

                                    px-3.5

                                    bg-white

                                    border
                                    border-antique-200

                                    flex
                                    items-center
                                    gap-3

                                    text-xs
                                    text-warm-800
                                "
                            >

                                <i
                                    class="
                                        fa-regular
                                        fa-envelope

                                        text-antique-500

                                        w-4
                                    "
                                ></i>


                                <span
                                    class="truncate"
                                >
                                    <?= e(
                                        $emailDonatur
                                        ?: 'Belum diisi'
                                    ) ?>
                                </span>

                            </div>

                        </div>



                        <!-- WHATSAPP -->

                        <div>

                            <label
                                class="
                                    block

                                    text-[11px]

                                    font-bold

                                    text-antique-500

                                    mb-2
                                "
                            >
                                Nomor WhatsApp
                            </label>


                            <div
                                class="
                                    min-h-[47px]

                                    rounded-xl

                                    px-3.5

                                    bg-white

                                    border
                                    border-antique-200

                                    flex
                                    items-center
                                    gap-3

                                    text-xs
                                    text-warm-800
                                "
                            >

                                <i
                                    class="
                                        fa-brands
                                        fa-whatsapp

                                        text-antique-500

                                        w-4
                                    "
                                ></i>


                                <?= e(
                                    $noHpDonatur
                                    ?: 'Belum diisi'
                                ) ?>

                            </div>

                        </div>



                        <!-- FOTO -->

                        <div>

                            <label
                                class="
                                    block

                                    text-[11px]

                                    font-bold

                                    text-antique-500

                                    mb-2
                                "
                            >
                                Foto Profil
                            </label>


                            <button
                                type="button"

                                onclick="openEditModal(true)"

                                class="
                                    w-full

                                    min-h-[47px]

                                    rounded-xl

                                    px-3.5

                                    bg-white

                                    border
                                    border-antique-200

                                    flex
                                    items-center
                                    gap-3

                                    text-xs
                                    text-warm-800

                                    hover:border-antique-500

                                    transition
                                "
                            >

                                <i
                                    class="
                                        fa-solid
                                        fa-camera

                                        text-antique-500

                                        w-4
                                    "
                                ></i>

                                <?= $avatarUrl
                                    ? 'Ganti Foto Profil'
                                    : 'Tambahkan Foto Profil' ?>

                            </button>

                        </div>

                    </div>

                </div>

            </section>
