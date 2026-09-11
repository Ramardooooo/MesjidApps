            <section
                class="
                    profile-hero

                    rounded-[24px]

                    border
                    border-antique-500/30

                    shadow-luxury

                    overflow-hidden

                    px-6
                    sm:px-9

                    py-7
                    sm:py-8
                "
            >

                <div
                    class="
                        flex
                        items-center

                        gap-5
                        sm:gap-6
                    "
                >


                    <!-- FOTO PROFIL -->

                    <div
                        class="
                            relative
                            shrink-0
                        "
                    >

                        <?php if ($avatarUrl): ?>

                            <img
                                id="heroAvatar"

                                src="<?= e($avatarUrl) ?>"

                                alt="Foto profil"

                                class="
                                    profile-photo

                                    w-[88px]
                                    h-[88px]

                                    sm:w-[94px]
                                    sm:h-[94px]

                                    rounded-2xl

                                    object-cover

                                    border-2
                                    border-antique-500/60
                                "
                            >

                        <?php else: ?>

                            <div
                                id="heroAvatar"

                                class="
                                    profile-photo

                                    w-[88px]
                                    h-[88px]

                                    sm:w-[94px]
                                    sm:h-[94px]

                                    rounded-2xl

                                    bg-cypress-800

                                    border-2
                                    border-antique-500/60

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


                        <!-- CAMERA BUTTON -->

                        <button
                            type="button"

                            onclick="openEditModal(true)"

                            class="
                                photo-upload-button
                            "
                            title="Ganti foto profil"
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-camera

                                    text-xs
                                "
                            ></i>

                        </button>

                    </div>



                    <!-- TEXT -->

                    <div
                        class="min-w-0"
                    >

                        <div
                            class="
                                text-antique-500

                                text-xs
                                sm:text-sm

                                font-medium

                                mb-1.5
                            "
                        >
                            ASSALAMU'ALAIKUM
                        </div>


                        <h1
                            class="
                                text-2xl
                                sm:text-[30px]

                                leading-tight

                                text-white

                                font-bold

                                tracking-wide

                                truncate
                            "
                        >
                            <?= e($namaDonatur) ?>
                        </h1>


                        <div
                            class="
                                flex
                                flex-wrap

                                items-center

                                gap-x-4
                                gap-y-1.5

                                mt-3

                                text-[11px]

                                text-emerald-100/90
                            "
                        >

                            <span
                                class="
                                    inline-flex
                                    items-center
                                    gap-1.5
                                "
                            >

                                <i
                                    class="
                                        fa-regular
                                        fa-heart

                                        text-antique-500
                                    "
                                ></i>

                                Donatur

                            </span>


                            <span
                                class="
                                    text-white/20
                                "
                            >
                                |
                            </span>


                            <span>
                                Member sejak
                                <?= e(
                                    date(
                                        'Y',
                                        strtotime(
                                            $tanggalDaftar
                                        )
                                    )
                                ) ?>
                            </span>

                        </div>

                    </div>

                </div>

            </section>
