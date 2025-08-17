module.exports = {
  ci: {
    collect: {
      numberOfRuns: 1,
      url: ['http://localhost:8000'],
      settings: {
        chromeFlags: '--no-sandbox --disable-dev-shm-usage --disable-gpu',
      }
    },
    assert: {
      assertions: {
        'installable-manifest': ['error', { minScore: 1 }],
        'service-worker': ['error', { minScore: 1 }],
        'categories:pwa': ['warn', { minScore: 0.8 }]
      }
    }
  }
};
