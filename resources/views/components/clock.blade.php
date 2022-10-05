<div class="p-2 overflow-hidden w-98 border-1">
    <div class="flex justify-center mx-auto mt-2">
        <div id="ct7" class="w-full text-2xl text-center bg-white rounded text-black-100 md:text-2xl lg:text-2xl"></div>
    </div>
</div>

<script>
        <!-- https://www.plus2net.com/javascript_tutorial/clock.php -->
        function display_ct7() {
                var x = new Date()
                var ampm = x.getHours() >= 12 ? ' PM' : ' AM';
        // Uncomment to get 12 hours AM/PM Mode
        hours = x.getHours() // % 12;
        // hours = hours ? hours : 12;
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
        x1 = x1 + " - " + hours + ":" + minutes + ":" + seconds // + " " + ampm;
        document.getElementById('ct7').innerHTML = x1;
        display_c7();
        }
        function display_c7() {
                var refresh = 1000; // Refresh rate in milli seconds
        mytime = setTimeout('display_ct7()', refresh)
    }
    display_c7()
</script>