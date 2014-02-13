define("default/js/lib/sandbox", function(){

    // list of all page states
    var _states = {
        logged : false
    };
    var _events = {};
    // get state value by key
    function stateGet (stateKey) {
        return _states[stateKey];
    }
    // add/set enabled state
    function _stateSetOn  (stateKey) {
        _stateSet(stateKey, true);
    }
    // add/set disabled state
    function _stateSetOff (stateKey) {
        _stateSet(stateKey, false);
    }
    // set/add statue value
    function _stateSet (stateKey, stateValue) {
        _states[stateKey] = stateValue;
    }

    // Sandbox
    var _Sandbox = {
        stateGet: stateGet,
        stateSetOn: _stateSetOn,
        stateSetOff: _stateSetOff,
        // subscribe on eventID
        eventSubscribe : function (eventID, listener) {
            if (!_events[eventID])
                _events[eventID] = [];

            var listenerHash = _app.Utils.hashCode(listener);
            var alreadyAdded = false;
            // avoid duplicates
            for (var i = 0, len = _events[eventID].length; i < len && !alreadyAdded; i++)
                alreadyAdded = (_events[eventID][i].id === listenerHash);

            // add another listener
            if (!alreadyAdded) {
                // _app.log(true, 'adding subscriber on ', eventID);
                _events[eventID].push({
                    id : listenerHash,
                    fn : listener
                });
                return true;
            } else 
                ;//_app.log(true, 'this subscriber is already added on ', eventID);
            return false;
        },
        // remove callback subscription to eventID 
        eventUnsubscribe : function (eventID, listener) {
            if (!_events[eventID])
                return false;
            var listenerHash = _app.Utils.hashCode(listener);
            var unsubcribeCount = 0;
            for (var i = 0, len = _events[eventID].length; i < len; i++)
                if (_events[eventID][i].id === listenerHash) {
                    _events[eventID].splice(i, 1);
                    unsubcribeCount++;
                }
            return unsubcribeCount > 0;
        },
        eventNotify : function (eventID, data, callback) {
            var listeners = _events[eventID];
            if (!listeners)
                return;
            var results = [];
            var rez = null;
            // _app.log(true, 'eventNotify: >>>>>>>>>>>>> ', eventID, ' >>>>>>>>> ');
            // _app.log(true, listeners);
            // _app.log(true, 'eventNotify: <<<<<<<<<<<<< ', eventID, ' <<<<<<<<< ');
            // loop through listeners
            for (var i = 0, len = _events[eventID].length; i < len; i++)
                results.push(listeners[i].fn(data));
            // adjust result
            if (results.length == 1)
                rez = results.pop();
            else
                rez = results;
            // _app.log(true, 'eventNotify: has result', rez, results);
            // perform callback with results
            if (typeof callback === "function")
                callback(null, rez);
            return rez;
        }
    };

    return _Sandbox;

});