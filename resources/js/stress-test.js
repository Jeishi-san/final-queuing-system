// stress-helpers.js
module.exports = {
  uuid: function(userContext, events, done) {
    // simple uuid v4 generator
    const uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
      const r = (Math.random() * 16) | 0;
      const v = c === 'x' ? r : (r & 0x3) | 0x8;
      return v.toString(16);
    });
    userContext.vars.uuid = uuid;
    return done();
  },
  timestamp: function (userContext, events, done) {
    userContext.vars.timestamp = Date.now();
    userContext.vars.now = new Date().toISOString();
    done();
  }
};
