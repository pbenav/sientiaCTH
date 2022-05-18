<div class="w-98 owrflow-hidden p-2 border-1">
    <div class="flex justify-center mt-2 mx-auto">
        <div><h2 class="mx-1 text-sm md:text-sm lg:text-sm uppercase">Hora del sistema</h2></div>
        <div id="ct7" class="px-1 text-sm md:text-sm lg:text-sm bg-blue-500 text-gray-100 rounded"></div>
    </div>
</div>

<script>
        <!-- https://www.plus2net.com/javascript_tutorial/clock.php -->
        function display_ct7() {
                var x = new Date()
                var ampm = x.getHours() >= 12 ? ' PM' : ' AM';
        hours = x.getHours() % 12;
        hours = hours ? hours : 12;
        hours = hours.toString().length == 1 ? 0 + hours.toString() : hours;
        var minutes = x.getMinutes().toString()
                minutes = minutes.length == 1 ? 0 + minutes : minutes;
        var seconds = x.getSeconds().toString()
                seconds = seconds.length == 1 ? 0 + seconds : seconds;
        var month = (x.getMonth() + 1).toString();
        month = month.length == 1 ? 0 + month : month;
        var dt = x.getDate().toString();
        dt = dt.length == 1 ? 0 + dt : dt;
        var x1 = month + "/" + dt + "/" + x.getFullYear();
        x1 = x1 + " - " + hours + ":" + minutes + ":" + seconds + " " + ampm;
        document.getElementById('ct7').innerHTML = x1;
        display_c7();
        }
        function display_c7() {
                var refresh = 1000; // Refresh rate in milli seconds
        mytime = setTimeout('display_ct7()', refresh)
    }
    display_c7()
</script>