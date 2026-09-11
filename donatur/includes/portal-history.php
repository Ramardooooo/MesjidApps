            <section
                class="
                    mt-5
                    sm:mt-6

                    bg-white/95

                    rounded-[22px]

                    border
                    border-antique-300/50

                    shadow-soft

                    overflow-hidden
                "
            >

                <div
                    class="
                        px-5
                        sm:px-6

                        py-5

                        border-b
                        border-antique-200

                        flex
                        items-center
                        justify-between
                    "
                >

                    <div>

                        <h2
                            class="
                                text-sm
                                sm:text-base

                                font-bold

                                text-cypress-800
                            "
                        >
                            Riwayat Infaq
                        </h2>


                        <p
                            class="
                                text-[11px]

                                text-warm-800/55

                                mt-1
                            "
                        >
                            Catatan donasi yang telah kamu lakukan.
                        </p>

                    </div>


                    <span
                        class="
                            text-[10px]

                            px-3
                            py-1.5

                            rounded-full

                            bg-antique-50

                            border
                            border-antique-200

                            text-antique-700

                            font-bold
                        "
                    >
                        <?= $jumlahTransaksi ?>
                        Donasi
                    </span>

                </div>



                <?php if (
                    empty($riwayatDonasi)
                ): ?>

                    <div
                        class="
                            text-center

                            py-10
                        "
                    >

                        <i
                            class="
                                fa-solid
                                fa-receipt

                                text-3xl

                                text-stone-300
                            "
                        ></i>


                        <p
                            class="
                                text-xs

                                text-warm-800/55

                                mt-3
                            "
                        >
                            Belum ada catatan donasi.
                        </p>


                        <a
                            href="../home/donasi-online.php"

                            class="
                                inline-flex

                                items-center
                                gap-2

                                mt-4

                                px-4
                                py-2.5

                                rounded-xl

                                bg-cypress-700

                                text-white

                                text-xs

                                font-semibold
                            "
                        >

                            <i
                                class="
                                    fa-solid
                                    fa-hand-holding-heart
                                "
                            ></i>

                            Salurkan Infaq

                        </a>

                    </div>

                <?php else: ?>

                    <div
                        class="
                            overflow-x-auto
                        "
                    >

                        <table
                            class="
                                w-full

                                text-left

                                text-xs
                            "
                        >

                            <thead>

                                <tr
                                    class="
                                        border-b
                                        border-antique-200

                                        text-[10px]
                                        uppercase

                                        text-warm-800/55
                                    "
                                >

                                    <th
                                        class="
                                            px-5
                                            py-3
                                        "
                                    >
                                        Donasi
                                    </th>

                                    <th
                                        class="
                                            py-3
                                            pr-4
                                        "
                                    >
                                        Program
                                    </th>

                                    <th
                                        class="
                                            py-3
                                            pr-4
                                        "
                                    >
                                        Nominal
                                    </th>

                                    <th
                                        class="
                                            py-3
                                            pr-5
                                        "
                                    >
                                        Status
                                    </th>

                                    <th
                                        class="
                                            py-3
                                            pr-5
                                        "
                                    >
                                        Bukti
                                    </th>

                                </tr>

                            </thead>


                            <tbody
                                class="
                                    divide-y
                                    divide-antique-100
                                "
                            >

                                <?php foreach (
                                    $riwayatDonasi
                                    as $r
                                ): ?>

                                    <tr
                                        class="
                                            hover:bg-warm-50/70
                                            transition
                                        "
                                    >

                                        <td
                                            class="
                                                px-5
                                                py-4
                                            "
                                        >

                                            <span
                                                class="
                                                    block

                                                    font-mono
                                                    font-bold

                                                    text-cypress-800
                                                "
                                            >
                                                <?= e(
                                                    $r['no_donasi']
                                                ) ?>
                                            </span>


                                            <span
                                                class="
                                                    block

                                                    text-[10px]

                                                    text-warm-800/45

                                                    mt-1
                                                "
                                            >
                                                <?= tanggal_indo(
                                                    $r['tanggal_donasi']
                                                ) ?>
                                            </span>

                                        </td>


                                        <td
                                            class="
                                                py-4
                                                pr-4
                                            "
                                        >

                                            <span
                                                class="
                                                    block

                                                    font-semibold

                                                    text-warm-900
                                                "
                                            >
                                                <?= e(
                                                    $r['nama_program']
                                                ) ?>
                                            </span>


                                            <span
                                                class="
                                                    text-[10px]

                                                    text-warm-800/50
                                                "
                                            >
                                                <?= e(
                                                    $r['metode_pembayaran']
                                                ) ?>
                                            </span>

                                        </td>


                                        <td
                                            class="
                                                py-4
                                                pr-4

                                                whitespace-nowrap
                                            "
                                        >

                                            <span
                                                class="
                                                    font-bold

                                                    text-cypress-700
                                                "
                                            >
                                                <?= format_rupiah(
                                                    $r['nominal']
                                                ) ?>
                                            </span>

                                        </td>


                                        <td
                                            class="
                                                py-4
                                                pr-5
                                            "
                                        >

                                            <?php if (
                                                $r['status'] ===
                                                'diverifikasi'
                                            ): ?>

                                                <span
                                                    class="
                                                        px-2.5
                                                        py-1

                                                        rounded-full

                                                        text-[10px]

                                                        font-bold

                                                        bg-emerald-100

                                                        text-emerald-800
                                                    "
                                                >
                                                    Diterima
                                                </span>

                                            <?php elseif (
                                                $r['status'] ===
                                                'ditolak'
                                            ): ?>

                                                <span
                                                    class="
                                                        px-2.5
                                                        py-1

                                                        rounded-full

                                                        text-[10px]

                                                        font-bold

                                                        bg-red-100

                                                        text-red-800
                                                    "
                                                >
                                                    Ditolak
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="
                                                        px-2.5
                                                        py-1

                                                        rounded-full

                                                        text-[10px]

                                                        font-bold

                                                        bg-amber-100

                                                        text-amber-800
                                                    "
                                                >
                                                    Menunggu
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td
                                            class="
                                                py-4
                                                pr-5
                                            "
                                        >
                                            <a
                                                href="kwitansi.php?no=<?= urlencode($r['no_donasi']) ?>"
                                                class="
                                                    inline-flex
                                                    items-center
                                                    gap-1.5
                                                    px-2.5
                                                    py-1.5
                                                    rounded-lg
                                                    bg-cypress-700
                                                    hover:bg-cypress-800
                                                    text-white
                                                    text-[10px]
                                                    font-semibold
                                                    transition
                                                "
                                                title="Lihat bukti donasi"
                                            >
                                                <i class="fa-solid fa-receipt"></i>
                                                <span>Lihat</span>
                                            </a>
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </section>
