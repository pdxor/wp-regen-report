<?php
get_header();
?>

<div class="mx-auto px-4 py-8">
    <header class="bg-green-800 text-white p-8 rounded-lg mb-12 text-center">
        <h1 class="text-3xl"><?php the_title(); ?> yy</h1>
    </header>

    <div class="tabs flex justify-center flex-wrap gap-4 mb-8">
        <button class="tab active px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'environmental')">Environmental Information</button>
        <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'agricultural')">Agricultural Resources</button>
        <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'cultural')">Cultural & Historical</button>
        <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'building')">Building & Zoning</button>
        <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'business')">Business & Economic</button>
    </div>

    <div id="environmental" class="tab-content active bg-white p-8 rounded-lg shadow mb-8">
        <?php echo wpautop(get_post_meta(get_the_ID(), '_environmental_info', true)); ?>
    </div>

    <div id="agricultural" class="tab-content hidden bg-white p-8 rounded-lg shadow mb-8">
        <?php echo wpautop(get_post_meta(get_the_ID(), '_agricultural_resources', true)); ?>
    </div>

    <div id="cultural" class="tab-content hidden bg-white p-8 rounded-lg shadow mb-8">
        <?php echo wpautop(get_post_meta(get_the_ID(), '_cultural_historical', true)); ?>
    </div>

    <div id="building" class="tab-content hidden bg-white p-8 rounded-lg shadow mb-8">
        <?php echo wpautop(get_post_meta(get_the_ID(), '_building_zoning', true)); ?>
    </div>

    <div id="business" class="tab-content hidden bg-white p-8 rounded-lg shadow mb-8">
        <?php echo wpautop(get_post_meta(get_the_ID(), '_business_economic', true)); ?>
    </div>
</div>

<script>
function openTab(evt, tabName) {
    const tabContents = document.getElementsByClassName("tab-content");
    for (let content of tabContents) {
        content.classList.add('hidden');
        content.classList.remove('active');
    }
    
    const tabs = document.getElementsByClassName("tab");
    for (let tab of tabs) {
        tab.classList.remove('active', 'bg-green-800', 'text-white');
    }
    
    document.getElementById(tabName).classList.remove('hidden');
    document.getElementById(tabName).classList.add('active');
    evt.currentTarget.classList.add('active', 'bg-green-800', 'text-white');
}

// Activate first tab by default
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('.tab').click();
});
</script>
<style>
#et-main-area>div>div {
    box-shadow: 0 2px 10px var(--yzfy-card-secondary-bg-color);
    border-width: 1px;
    color: black!important;
    text-align: center;
    padding-bottom: 28px;
}
.mx-auto {
    background-color: #00000082 !important;
    color: #f4ebd8 !important;
    font-size: 22px;
    border: 15px solid #d8b670;
}
input, button, select, textarea {
    font-family: inherit;
    font-size: inherit;
    line-height: inherit;
    border-radius: 13px;
    background-color: #d8b670;
    padding: 5px 15px;
    /* text-align: center; */
    margin: 15px;
    /* margin-top: -39px; */
}
.tab-content p, .tab-content ul li {
	/* color: #d8b670!important; */
    text-align: left;
}
.tab-content ul li {
	padding: 12px;
	padding-left: 32px;
}
.tab-content ul {
background:#333;
	padding: 35px 11px;
	margin: 0px 30px;
	border-radius:13px;
	margin-bottom:33px
}

</style>
<?php
get_footer();
?>
