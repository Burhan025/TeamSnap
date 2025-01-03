// Move Icon Div to top level
jQuery(document).ready(function() {
  jQuery(".review-slider-BA .pp-testimonials-wrap .owl-nav").prependTo(".review-slider-BA .pp-testimonials .pp-content-wrapper");
});


// Executing script on Home page for sports club and team management sections
jQuery(document).ready(function($) {

  $('.callout-hover-row .fl-col-group').each(function() {

      var $group = $(this); // Current group

      // Select the first item by default
      $group.find('.ro-swap-triggers .fl-module-accordion').first().addClass('active');
      $group.find('.ro-swap-imgs .fl-module-photo').hide().first().show();

      $group.find('.ro-swap-triggers .fl-accordion-button').on('click', function() {
          
          var $accordion = $(this).closest('.fl-module-accordion');
          var index = $accordion.index('.ro-swap-triggers .fl-module-accordion');

          $group.find('.ro-swap-imgs .fl-module-photo').hide();
          $group.find('.ro-swap-imgs .fl-module-photo').eq(index).show();

          // Remove active class from all items
          $group.find('.ro-swap-triggers .fl-module-accordion').removeClass('active');

          // Add active class to the selected item
          $accordion.addClass('active');
      });
  });
});

jQuery(document).ready(function($) {
  // Check if `.components-flex` has a child with class `.components-flex-item`
  $('.components-flex').each(function() {
      if ($(this).find('.components-flex-item').length > 0) {
          // If it has the child, do something
          console.log('Found `.components-flex-item` inside `.components-flex`');
          
          // Example: Apply CSS to `.components-flex` itself
          $(this).css('border', '2px solid red');
          
          // Example: Apply CSS to `.components-flex-item`
          $(this).find('.components-flex-item').css('background-color', 'lightblue');
          
          // Example: Add a click event handler to `.components-flex-item`
          $(this).find('.components-flex-item').click(function() {
              console.log('Clicked on `.components-flex-item` inside `.components-flex`');
          });
      } else {
          console.log('No `.components-flex-item` found inside `.components-flex`');
      }
  });
});


// Mobile Menu Script 
jQuery(document).ready(function($){
  $('#ts-main-mega-menu .mega-toggle-block').click(function() { 
      var mainHeader = $('header');
      if ($('#ts-main-mega-menu .mega-menu-toggle').hasClass('mega-menu-open')) {
          $(mainHeader).removeClass('header-active');
          $(mainHeader).addClass('header-non-active');
      } else {
          $(mainHeader).addClass('header-active');
          $(mainHeader).removeClass('header-non-active');
      }
  });
});

// Executing script on Home page for sports club and team management sections
jQuery(document).ready(function($) {

  $('.callout-hover-row .fl-col-group').each(function() {

      var $group = $(this); // Current group

      // Select the first item by default
      $group.find('.ro-swap-triggers .fl-module-callout').first().addClass('active');

      $group.find('.ro-swap-triggers .fl-module-callout').on('mouseenter', function() {
          
          var index = $(this).index();

          $group.find('.ro-swap-imgs .fl-module-photo').hide();
          $group.find('.ro-swap-imgs .fl-module-photo').eq(index).show();

          // Remove active class from all items
          $group.find('.ro-swap-triggers .fl-module-callout').removeClass('active');

          // Add active class to the selected item
          $(this).addClass('active');
      });
  });
});

// Limits the number of text characters in a breadcrumbs
jQuery(document).ready(function($){
  function limitBreadcrumb(selector, limit) {
      $(selector).each(function() {
          var text = $(this).text();
          if (text.length > limit) {
              $(this).text(text.substring(0, limit) + '...');
          }
      });
  }
  // Call the function and pass the element selector and limit
  limitBreadcrumb('.ContentHub--Breadcrumbs .active', 40);
});

// Toggle Button for Resource Pages
jQuery(document).ready(function($){
  $("#toggleButton_As .fl-button-wrap a.fl-button").click(function(event){
      event.preventDefault(); // Prevent the default behavior
      $("#coachesArchivePosts_As .pp-post-filters-wrapper").slideToggle("slow"); // Toggle the content visibility
  });
});

// Coaches Corner Filters
jQuery(document).ready(function($){
  // Variable to track if prepend has been done
  var prependSport = false;
  var prependType = false;

  // Check each filter value
  $("#coachesArchivePosts_As .pp-post-filters-wrapper ul.pp-post-filters li").each(function(){
      var filterValue = $(this).data("filter"); // Get the data-filter value

      // Check if data-filter starts with ".sport"
      if (filterValue.startsWith(".sport")) {
          prependSport = true; // Set flag to true once prepend is done
      }
      if (filterValue.startsWith(".resource-type")) {
          prependType = true; // Set flag to true once prepend is done
      }
  });

  // Check if prependSport is true and prepend only once
  if (prependSport) {
      var createEl = $("#coachesArchivePosts_As .pp-post-filters-wrapper");
      $(createEl).addClass("sport-filtered");
  }
  // Check if prependType is true and prepend only once
  if (prependType) {
      var createEl = $("#coachesArchivePosts_As .pp-post-filters-wrapper");
      $(createEl).addClass("type-filtered");
  }
});

// On-click trigger video lighbox on Take a Tour of Teamsnap for Business Page
jQuery(document).ready(function($) {
  $(".teamsnap-for-business-hero-dual-btn-as .fl-button-group-buttons .fl-button-group-button-pai1bljnxmfy-0 a.fl-button").click(function(event) {
          event.preventDefault(); // Prevent the default behavior
      $(".teamsnap-for-business-video-gallery-as .pp-video-gallery-item:nth-child(1) .pp-video-image-overlay").trigger('click');
  });
});

// Toggle Button for Team Features Pages
jQuery(document).ready(function($){
  $("#allFeaturesMobileToggleBtn_AS").click(function(event){
      event.preventDefault(); // Prevent the default behavior
      $("#allFeaturesLinks_AS").slideToggle("slow"); // Toggle the content visibility
  });
});


jQuery(document).ready(function($) {
	// Assuming you want to add a class to the parent of an element with the class 'social-share'
	$('.social-share').parent().addClass('parent-of-social-share');
	
	const swiper = new Swiper('.swiper-container', {
	  // Optional parameters
	  direction: 'horizontal',
	  loop: true,
	  
	  autoplay: {
		  delay: 4500,
		  disableOnInteraction: true,
		},
	
	  // If we need pagination
	  pagination: {
		el: '.swiper-pagination',
	  },
	
	  // Navigation arrows
	  navigation: {
		nextEl: '.swiper-button-next',
		prevEl: '.swiper-button-prev',
	  },
	
	  // And if we need scrollbar
	  scrollbar: {
		el: '.swiper-scrollbar',
	  },
	});
	$('.facetwp-radio').click(function() {
		// Remove the 'checked' class from all radios
		$('.facetwp-radio').removeClass('checked');
	
		// Add the 'checked' class to the clicked radio
		$(this).addClass('checked');
	
		// Optional: You can perform additional actions here, such as triggering an AJAX call
		// to filter results based on the selection or updating hidden form inputs.
	});


	/**
	 * Trigger modal popup when a query parameter 'video' is found in the URL.
	 * Example: https://teamsnapsite.wpenginepowered.com/for-business-resource-library/take-a-tour-of-teamsnap-for-business?video=2
	 */
    // Function to get query parameters from the URL
    function getQueryParameter(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // Function to trigger the video popup
    function triggerVideoPopup(index) {
        // Find the video overlay element with the specified data-index
        const $videoOverlay = $('.pp-video-gallery-item[data-index="' + index + '"] .pp-video-image-overlay');
        
        if ($videoOverlay.length) {
            // Simulate a click to open the video popup
            $videoOverlay.trigger('click');
        }
    }

    // Get the 'video' query parameter
    const videoIndex = getQueryParameter('video');
    
    // If the video parameter exists, trigger the corresponding video popup
    if (videoIndex) {
        // Convert video parameter to zero-based index (e.g., '1' becomes 0)
        const index = parseInt(videoIndex, 10) - 1;
        
        // Trigger the corresponding video popup based on the index
        if (!isNaN(index) && index >= 0) {
            // triggerVideoPopup(index);
        }
    }

});


// Move Icon Div to top level
jQuery(document).ready(function() {
  jQuery(".review-slider-BA .pp-testimonials-wrap .owl-nav").prependTo(".review-slider-BA .pp-testimonials .pp-content-wrapper");
});


// Executing script on Home page for sports club and team management sections
jQuery(document).ready(function($) {

  $('.callout-hover-row .fl-col-group').each(function() {

      var $group = $(this); // Current group

      // Select the first item by default
      $group.find('.ro-swap-triggers .fl-module-accordion').first().addClass('active');
      $group.find('.ro-swap-imgs .fl-module-photo').hide().first().show();

      $group.find('.ro-swap-triggers .fl-accordion-button').on('click', function() {
          
          var $accordion = $(this).closest('.fl-module-accordion');
          var index = $accordion.index('.ro-swap-triggers .fl-module-accordion');

          $group.find('.ro-swap-imgs .fl-module-photo').hide();
          $group.find('.ro-swap-imgs .fl-module-photo').eq(index).show();

          // Remove active class from all items
          $group.find('.ro-swap-triggers .fl-module-accordion').removeClass('active');

          // Add active class to the selected item
          $accordion.addClass('active');
      });
  });
});

jQuery(document).ready(function($) {
  // Check if `.components-flex` has a child with class `.components-flex-item`
  $('.components-flex').each(function() {
      if ($(this).find('.components-flex-item').length > 0) {
          // If it has the child, do something
          console.log('Found `.components-flex-item` inside `.components-flex`');
          
          // Example: Apply CSS to `.components-flex` itself
          $(this).css('border', '2px solid red');
          
          // Example: Apply CSS to `.components-flex-item`
          $(this).find('.components-flex-item').css('background-color', 'lightblue');
          
          // Example: Add a click event handler to `.components-flex-item`
          $(this).find('.components-flex-item').click(function() {
              console.log('Clicked on `.components-flex-item` inside `.components-flex`');
          });
      } else {
          console.log('No `.components-flex-item` found inside `.components-flex`');
      }
  });
});


// Mobile Menu Script 
jQuery(document).ready(function($){
  $('#ts-main-mega-menu .mega-toggle-block').click(function() { 
      var mainHeader = $('header');
      if ($('#ts-main-mega-menu .mega-menu-toggle').hasClass('mega-menu-open')) {
          $(mainHeader).removeClass('header-active');
          $(mainHeader).addClass('header-non-active');
      } else {
          $(mainHeader).addClass('header-active');
          $(mainHeader).removeClass('header-non-active');
      }
  });
});

// Executing script on Home page for sports club and team management sections
jQuery(document).ready(function($) {

  $('.callout-hover-row .fl-col-group').each(function() {

      var $group = $(this); // Current group

      // Select the first item by default
      $group.find('.ro-swap-triggers .fl-module-callout').first().addClass('active');

      $group.find('.ro-swap-triggers .fl-module-callout').on('mouseenter', function() {
          
          var index = $(this).index();

          $group.find('.ro-swap-imgs .fl-module-photo').hide();
          $group.find('.ro-swap-imgs .fl-module-photo').eq(index).show();

          // Remove active class from all items
          $group.find('.ro-swap-triggers .fl-module-callout').removeClass('active');

          // Add active class to the selected item
          $(this).addClass('active');
      });
  });
});

// Limits the number of text characters in a breadcrumbs
jQuery(document).ready(function($){
  function limitBreadcrumb(selector, limit) {
      $(selector).each(function() {
          var text = $(this).text();
          if (text.length > limit) {
              $(this).text(text.substring(0, limit) + '...');
          }
      });
  }
  // Call the function and pass the element selector and limit
  limitBreadcrumb('.ContentHub--Breadcrumbs .active', 40);
});

// Toggle Button for Resource Pages
jQuery(document).ready(function($){
  $("#toggleButton_As .fl-button-wrap a.fl-button").click(function(event){
      event.preventDefault(); // Prevent the default behavior
      $("#coachesArchivePosts_As .pp-post-filters-wrapper").slideToggle("slow"); // Toggle the content visibility
  });
});

// Coaches Corner Filters
jQuery(document).ready(function($){
  // Variable to track if prepend has been done
  var prependSport = false;
  var prependType = false;

  // Check each filter value
  $("#coachesArchivePosts_As .pp-post-filters-wrapper ul.pp-post-filters li").each(function(){
      var filterValue = $(this).data("filter"); // Get the data-filter value

      // Check if data-filter starts with ".sport"
      if (filterValue.startsWith(".sport")) {
          prependSport = true; // Set flag to true once prepend is done
      }
      if (filterValue.startsWith(".resource-type")) {
          prependType = true; // Set flag to true once prepend is done
      }
  });

  // Check if prependSport is true and prepend only once
  if (prependSport) {
      var createEl = $("#coachesArchivePosts_As .pp-post-filters-wrapper");
      $(createEl).addClass("sport-filtered");
  }
  // Check if prependType is true and prepend only once
  if (prependType) {
      var createEl = $("#coachesArchivePosts_As .pp-post-filters-wrapper");
      $(createEl).addClass("type-filtered");
  }
});

// On-click trigger video lighbox on Take a Tour of Teamsnap for Business Page
jQuery(document).ready(function($) {
  $(".teamsnap-for-business-hero-dual-btn-as .fl-button-group-buttons .fl-button-group-button-pai1bljnxmfy-0 a.fl-button").click(function(event) {
          event.preventDefault(); // Prevent the default behavior
      $(".teamsnap-for-business-video-gallery-as .pp-video-gallery-item:nth-child(1) .pp-video-image-overlay").trigger('click');
  });
});

// Toggle Button for Team Features Pages
jQuery(document).ready(function($){
  $("#allFeaturesMobileToggleBtn_AS").click(function(event){
      event.preventDefault(); // Prevent the default behavior
      $("#allFeaturesLinks_AS").slideToggle("slow"); // Toggle the content visibility
  });
});


jQuery(document).ready(function($) {
	// Assuming you want to add a class to the parent of an element with the class 'social-share'
	$('.social-share').parent().addClass('parent-of-social-share');
	
	const swiper = new Swiper('.swiper-container', {
	  // Optional parameters
	  direction: 'horizontal',
	  loop: true,
	  
	  autoplay: {
		  delay: 4500,
		  disableOnInteraction: true,
		},
	
	  // If we need pagination
	  pagination: {
		el: '.swiper-pagination',
	  },
	
	  // Navigation arrows
	  navigation: {
		nextEl: '.swiper-button-next',
		prevEl: '.swiper-button-prev',
	  },
	
	  // And if we need scrollbar
	  scrollbar: {
		el: '.swiper-scrollbar',
	  },
	});
	$('.facetwp-radio').click(function() {
		// Remove the 'checked' class from all radios
		$('.facetwp-radio').removeClass('checked');
	
		// Add the 'checked' class to the clicked radio
		$(this).addClass('checked');
	
		// Optional: You can perform additional actions here, such as triggering an AJAX call
		// to filter results based on the selection or updating hidden form inputs.
	});


	/**
	 * Trigger modal popup when a query parameter 'video' is found in the URL.
	 * Example: https://teamsnapsite.wpenginepowered.com/for-business-resource-library/take-a-tour-of-teamsnap-for-business?video=2
	 */
    // Function to get query parameters from the URL
    function getQueryParameter(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    // Function to trigger the video popup
    function triggerVideoPopup(index) {
        // Find the video overlay element with the specified data-index
        const $videoOverlay = $('.pp-video-gallery-item[data-index="' + index + '"] .pp-video-image-overlay');
        
        if ($videoOverlay.length) {
            // Simulate a click to open the video popup
            $videoOverlay.trigger('click');
        }
    }

    // Get the 'video' query parameter
    const videoIndex = getQueryParameter('video');
    
    // If the video parameter exists, trigger the corresponding video popup
    if (videoIndex) {
        // Convert video parameter to zero-based index (e.g., '1' becomes 0)
        const index = parseInt(videoIndex, 10) - 1;
        
        // Trigger the corresponding video popup based on the index
        if (!isNaN(index) && index >= 0) {
            // triggerVideoPopup(index);
        }
    }

});