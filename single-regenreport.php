<?php
get_header();
?>

<div class="container mx-auto px-4 py-8">
    <header class="bg-green-800 text-white p-8 rounded-lg mb-12 text-center">
        <h1 class="text-3xl"><?php the_title(); ?></h1>
    </header>

    <div class="tabs flex justify-center flex-wrap gap-4 mb-8">
        <button class="tab active px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'environmental')">Environmental Information</button>
        <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'agricultural')">Agricultural Resources</button>
        <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'cultural')">Cultural & Historical</button>
        <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'building')">Building & Zoning</button>
        <button class="tab px-8 py-4 bg-white rounded shadow hover:bg-green-800 hover:text-white transition-all" onclick="openTab(event, 'business')">Business & Economic</button>
    </div>

    <div id="environmental" class="tab-content active bg-white p-8 rounded-lg shadow mb-8">
        <?php 
        $description = get_post_meta(get_the_ID(), 'report_description', true);
        echo wp_kses_post($description);
        ?>
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

<?php
get_footer();
?>
