        </main>

        <!-- <footer class="admin-footer">

            <p>
                © 2026 School of Interdisciplinary Studies
            </p>

        </footer> -->

    </div>


    <script>

        const homepageToggle =
            document.getElementById('homepageToggle');

        const homepageSubmenu =
            document.getElementById('homepageSubmenu');


        homepageToggle.addEventListener('click', function () {

            homepageSubmenu.classList.toggle('open');

            homepageToggle.classList.toggle('active');

        });

    </script>

</body>

</html>