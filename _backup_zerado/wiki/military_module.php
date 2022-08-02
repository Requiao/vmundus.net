<?php
	include('head.php');
?>
	
	<div id="container">
		<p id="page_head">Military module.</p>
		
		<div id="post">
		<p style="text-align:right"><span style="font-size:20px"><em>Last update: Day 292.</em></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">Post order:</span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; 1 - <a href="index">Welcome to vMundus.</a></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; 2 - <a href="economy_module_part_I">Economy module. Part I.</a></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp;&nbsp;3 - <a href="economy_module_part_II">Economy module. Part II.</a></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; <strong>4 - <a href="military_module">Military module.</a></strong></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; 5 - <a href="political_module">Political&nbsp;module</a>.</span></span></p>

		<p>&nbsp;</p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; Every region has a level one &#39;Defense System&#39;.The country government can upgrade or repair&nbsp;&#39;Defense System&#39; if it was&nbsp;damaged after the battle. For this, a country will have to spend its own resources. The government can buy them from regular product market by clicking on the &#39;country&#39; button, for this they will use Ministry Budget. Ministry Budget will be allocated for different ministries by the government who have access to &#39;Budget Allocation&#39; law. Each minister will be able to use this money at their own discretion. Country products can be allocated in the same way as currency.</span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp;<img alt="product market offer" src="../wiki_img/product_market_offer.png" style="height:89px; width:800px" /></span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">Products are being placed in the&nbsp;ministry warehouse and can be used only by that ministry.</span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><img alt="ministry warehouse" src="../wiki_img/ministry_warehouse.png" style="height:131px; width:800px" /></span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><strong><span style="line-height:1.5em">Battlefield.</span></strong></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">This screenshot is from <a href="../en/active_battles">active battles</a> page:</span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><img alt="battlefield from active battles page" src="../wiki_img/battlefield_from_active_battles_page.png" style="height:286px; width:800px" /></span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; Left side is the attacker(red), on the right side is the defender(blue). When attacker starts a battle, he decides what attacking&nbsp;platform to use (higher level of the platform costs more resources but it has more strength, on the screenshot above, the attacker has a platform of 7000&nbsp;strength), battle budget, the price for 100 damage.</span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><img alt="start battle" src="../wiki_img/start_battle.png" style="height:307px; width:800px" />&nbsp;</span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">The defender will have a &#39;Defense System&#39; that is currently built in&nbsp;the attacked region. In this case 10 000. In order for the attacker to win the battle, he will need to destroy defender&#39;s defense system. The defender will win if he will&nbsp;destroy attacker&#39;s platform faster.&nbsp;The force bar, which is on top (currently 50% for the attacker and 50% for the defender) affects what&nbsp;damage a soldier can make. The enemy percentage is being subtracted from the soldier&#39;s damage.&nbsp;</span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><strong>Example</strong>: A soldier can make 2 damage without any equipment. But by using Riffle which adds him 4 extra damage and Ammo +4 damage, he can make 10 damage in total. For one hit a soldier gains 1 Combat Experience. 1 Combat Experience increases soldier&#39;s damage by 0.01 points. Since the force bar is 50% in the example, a soldier will&nbsp;be able to damage enemy platform or defense system&nbsp;by only 5 points.</span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">Formula:</span></span></p>

		<blockquote>
		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">TD = (BD + RD + AD) * FB</span></span></span></p>
		</blockquote>

		<p><span style="font-size:20px"><span style="line-height:1.5em">Where, <strong>TD</strong> - total damage, <strong>BD</strong> - person&#39;s base damage(2 and up, depends from experience), <strong>RD</strong> - riffle damage, <strong>AD</strong> - ammo damage, <strong>FB</strong> - force bar(0.0 ... 1.0) 0 is 0 % 1 is 100 %.</span></span></p>

		<p>&nbsp;</p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">Force bar is being calculated based on the total damage made by both sides.</span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><strong>Formula:</strong></span></span></p>

		<blockquote>
		<p><span style="font-size:20px"><span style="line-height:1.5em"><strong>&nbsp;&nbsp;</strong><span style="font-family:'Courier New',Courier,monospace">FB = (100/(AD + DD)) * AD; Attacker&#39;s force bar.</span></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">&nbsp;FB = (100/(AD + DD)) * DD;&nbsp; Defender&#39;s force bar.</span></span></span></p>
		</blockquote>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; Where, <strong>FB </strong>- force bar, <strong>AD </strong>- attackers total damage, <strong>DD </strong>- defenders total damage</span></span></p>

		<p>&nbsp;</p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><img alt="battlefield" src="../wiki_img/battlefield.png" style="height:172px; width:800px" /></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">Example. Attacker&#39;s force bar based on the picture above:</span></span></p>

		<blockquote>
		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">AD = 978.04; DD = 68.20</span></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">FB = (100 / (978.04 + 68.20)) * 978.04</span></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">&nbsp; &nbsp;= (100 / 1 046.24) * 978.04</span></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">&nbsp; &nbsp;= 93.48% </span><span style="font-family:'Times New Roman',Times,serif">attacker&#39;s force bar.</span></span></span></p>
		</blockquote>

		<p><span style="font-size:20px"><span style="line-height:1.5em">Example. Attacker&#39;s damage based on the 93.48% force bar:</span></span></p>

		<blockquote>
		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">TD = (BD + RD + AD) * FB</span></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">BD = 2; RD = 4; AD = 0; FB = 93.48% = 0.9348;</span></span></span></p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><span style="font-family:'Courier New',Courier,monospace">TD = (2 + 4 + 0) * 0.9348 = 6 * 0.9348 = 5.6</span></span></span></p>
		</blockquote>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">Based on the example above, the attacker will be able to damage defender&#39;s defense system only by 5.6 points instead of 6.</span></span></p>

		<p>&nbsp;</p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">During the battle, a soldier can be wounded. By using Body Armor, depending on the quality, it can reduce the chance to be wounded, which is 5%, by 1% and more. One wound increases soldier&#39;s chance to be killed by 1% (default is 0), but if a soldier is using Body Armor, it will reduce this chance by 1% and more depending on the quality. On a day change, a number of wounds will be reduced by 1 if a soldier is wounded more than 1 time.</span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px">&nbsp;<span style="line-height:1.5em"> In order for a person to fight in a battle, he will need to be trained. Training time last up to 8 hours, but if a person served in the army before, then his training time will be reduced by 120sec multiplied by his combat experience.</span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; Resistance Wars (RW). </span></span></p>

		<ul>
			<li><span style="font-size:20px"><span style="line-height:1.5em">Only users that have citizenship of that country can organize and support RW</span></span></li>
			<li><span style="font-size:20px"><span style="line-height:1.5em">In order for the battle to start, users must collect a specific amount of resources</span></span></li>
			<li><span style="font-size:20px"><span style="line-height:1.5em">If the user who organized RW will stop it, then the collected resources will be saved for the next RW</span></span></li>
			<li><span style="font-size:20px"><span style="line-height:1.5em">If defenders were upgrading their </span><span style="line-height:1.5em">defense</span><span style="line-height:1.5em"> system in the region when the battle started, then the unused resources will be returned back to the country(not ministry) warehouse</span></span></li>
			<li><span style="font-size:20px"><span style="line-height:1.5em">Attacking Platform level 1 with 5 000 strength will be used in all RW</span></span></li>
		</ul>

		<p><span style="font-size:20px"><img alt="region population" src="../wiki_img/region_population.png" style="height:130px; width:880px" /></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em">&nbsp; Soldiers are allowed to travel to countries that your country have &#39;Defence Agreement&#39; with. They will spend 20 energy to travel from one region to another.</span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px">&nbsp;<span style="line-height:1.5em"> If not all battle budget have been used during the battle, then the unused money will be returned back to the country treasury, not ministry budget. </span></span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px">&nbsp; If a region will be conquered, then citizens of the invader will be able to build companies in that region. By losing even all regions, the political module will not get affected, everything will remain the same, and elections will be scheduled on time, it will only affect countries economy.</span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px">&nbsp; You can attack only regions that are neighboring each other, use the map to find information about region neighbors, region owner, and region resources. If a region has access to the sea, then it can be attacked from any other region in the world with the&nbsp;sea access.</span></p>

		<p>&nbsp;</p>

		<p><span style="font-size:20px"><span style="line-height:1.5em"><strong>Next:&nbsp;</strong><a href="political_module">Political&nbsp;module.</a></span></span></p>

		</div>
	</div>
	
<?php include('../en/footer.php'); ?> 