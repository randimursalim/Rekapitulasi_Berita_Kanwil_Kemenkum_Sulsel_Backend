// Console Warning Suppression
(function() {
  const originalError = console.error;
  console.error = function(...args) {
    const message = args.join(' ');
    if (message.includes('Failed to decode downloaded font') || 
        message.includes('OTS parsing error') ||
        message.includes('unicons.iconscout.com')) {
      return; // Suppress font-related warnings
    }
    originalError.apply(console, args);
  };
  
  const originalWarn = console.warn;
  console.warn = function(...args) {
    const message = args.join(' ');
    if (message.includes('Failed to decode downloaded font') || 
        message.includes('OTS parsing error') ||
        message.includes('unicons.iconscout.com')) {
      return; // Suppress font-related warnings
    }
    originalWarn.apply(console, args);
  };
})();
