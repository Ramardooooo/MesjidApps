            <section
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

                <div
                    class="
                        p-5
                        sm:p-6
                    "
                >

                    <div
                        class="
                            flex
                            items-center

                            gap-4

                            mb-5
                        "
                    >

                        <div
                            class="
                                w-11
                                h-11

                                rounded-xl

                                bg-[#d9ad2f]

                                text-[#142a1f]

                                flex
                                items-center
                                justify-center
                            "
                        >

                            <i
                                class="
                                    fa-regular
                                    fa-circle-check
                                "
                            ></i>

                        </div>


                        <div>

                            <h2
                                class="
                                    text-sm
                                    sm:text-base

                                    font-bold

                                    text-antique-500
                                "
                            >
                                Status Akun
                            </h2>


                            <p
                                class="
                                    text-[11px]

                                    text-white/80

                                    mt-1
                                "
                            >
                                Informasi dasar akun donatur.
                            </p>

                        </div>

                    </div>



                    <div
                        class="
                            grid
                            grid-cols-1
                            lg:grid-cols-2

                            gap-3
                        "
                    >

                        <!-- STATUS -->

                        <div
                            class="
                                rounded-xl

                                bg-white/95

                                px-4
                                py-3.5
                            "
                        >

                            <div
                                class="
                                    text-[10px]

                                    uppercase
                                    tracking-wide

                                    text-warm-800/45

                                    mb-2
                                "
                            >
                                Status Akun
                            </div>


                            <div
                                class="
                                    flex
                                    items-center
                                    gap-2

                                    text-xs
                                    font-semibold

                                    <?= e($statusText) ?>
                                "
                            >

                                <span
                                    class="
                                        w-2
                                        h-2

                                        rounded-full

                                        <?= e($statusDot) ?>
                                    "
                                ></span>

                                <?= e($statusLabel) ?>

                            </div>

                        </div>



                        <!-- DAFTAR -->

                        <div
                            class="
                                rounded-xl

                                bg-white/95

                                px-4
                                py-3.5
                            "
                        >

                            <div
                                class="
                                    text-[10px]

                                    uppercase
                                    tracking-wide

                                    text-warm-800/45

                                    mb-2
                                "
                            >
                                Terdaftar Sejak
                            </div>


                            <div
                                class="
                                    text-xs

                                    font-semibold

                                    text-warm-900
                                "
                            >
                                <?= e(
                                    tanggal_indo(
                                        $tanggalDaftar
                                    )
                                ) ?>
                            </div>

                        </div>

                    </div>

                </div>

            </section>
