// Speedometer gauge module
(function(){
    function scaleCanvas(canvas) {
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        canvas.width = Math.max(1, Math.floor(rect.width * dpr));
        canvas.height = Math.max(1, Math.floor(rect.height * dpr));
        const ctx = canvas.getContext('2d');
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        return { ctx, width: rect.width, height: rect.height };
    }

    function drawGauge(canvas, value, opts) {
        const { ctx, width, height } = scaleCanvas(canvas);
        const centerX = width / 2;
        const centerY = height * (opts.centerYRatio || 0.82);
        const radius = Math.min(width, height * 1.8) * (opts.radiusRatio || 0.36);
        const lineWidth = radius * (opts.lineWidthRatio || 0.18);
        const startAngle = Math.PI;
        const endAngle = 2 * Math.PI;
        const totalAngle = endAngle - startAngle;
        const gaugeMax = opts.gaugeMax;
        const greenMax = opts.greenMax;
        const yellowMax = opts.yellowMax;

        ctx.clearRect(0, 0, width, height);
        ctx.lineCap = 'round';

        function drawArc(sA, eA, color) {
            ctx.beginPath();
            ctx.arc(centerX, centerY, radius, sA, eA);
            ctx.strokeStyle = color;
            ctx.lineWidth = lineWidth;
            ctx.stroke();
        }

        const greenEnd = startAngle + (greenMax / gaugeMax) * totalAngle;
        const yellowEnd = startAngle + (yellowMax / gaugeMax) * totalAngle;
        drawArc(startAngle, greenEnd, '#16a34a');
        drawArc(greenEnd, yellowEnd, '#f59e0b');
        drawArc(yellowEnd, endAngle, '#ef4444');

        // ticks
        ctx.fillStyle = '#6b7280';
        const tickFontRatio = (opts.tickFontRatio || 0.12);
        ctx.font = 'bold ' + Math.round(radius * tickFontRatio) + 'px sans-serif';
        const ticks = [0, greenMax, yellowMax, gaugeMax];
        ticks.forEach(function(t) {
            const angle = startAngle + (t / gaugeMax) * totalAngle;
            // place tick labels outside the arc for better readability
            const labelRadius = radius + lineWidth * 1.2;
            const x = centerX + Math.cos(angle) * labelRadius;
            const y = centerY + Math.sin(angle) * labelRadius;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(t.toString(), x, y);
        });

        // do NOT draw center value/cap here so we can render it after the needle (to avoid overlap)
        return { ctx, centerX, centerY, radius, lineWidth, startAngle, totalAngle };
    }

    function drawCenterValue(state, value, opts, progress) {
        const { ctx, centerX, centerY, radius, startAngle, totalAngle } = state;
        const greenMax = opts.greenMax;
        const yellowMax = opts.yellowMax;

        // center cap (keep at center)
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius * 0.1, 0, Math.PI * 2);
        ctx.fillStyle = '#1f2937';
        ctx.fill();

        // center value - position can be 'below' (default) or 'right' (dynamic)
        const valueColor = value <= greenMax ? '#16a34a' : (value <= yellowMax ? '#f59e0b' : '#ef4444');
        ctx.fillStyle = valueColor;
        ctx.font = '700 ' + Math.round(radius * (opts.centerFontRatio || 0.45)) + 'px sans-serif';

        if (opts.centerValuePosition === 'right') {
            // position the label to the right side of the needle based on current progress (0..1)
            const angle = startAngle + (typeof progress === 'number' ? progress : 0) * totalAngle;
            const labelDistance = radius * (opts.centerValueLabelDistanceRatio || 0.6);
            const perpOffset = radius * (opts.centerValuePerpOffsetRatio || 0.22);
            const x = centerX + Math.cos(angle) * labelDistance + Math.cos(angle + Math.PI / 2) * perpOffset;
            const y = centerY + Math.sin(angle) * labelDistance + Math.sin(angle + Math.PI / 2) * perpOffset;
            ctx.textAlign = 'left';
            ctx.textBaseline = 'middle';
            ctx.fillText(value.toFixed(1), x + (radius * 0.03), y);
        } else {
            const yOffsetRatio = (typeof opts.centerValueYOffsetRatio !== 'undefined') ? opts.centerValueYOffsetRatio : 0.12;
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(value.toFixed(1), centerX, centerY + radius * yOffsetRatio);
        }
    }

    function drawNeedle(state, progress, opts) {
        const { ctx, centerX, centerY, radius, lineWidth, startAngle, totalAngle } = state;
        // needle angle based on progress 0..1
        const angle = startAngle + progress * totalAngle;
        const needleLength = radius + lineWidth * 0.3;
        const needleWidth = radius * (opts.needleWidthRatio || 0.06);

        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(angle);
        ctx.shadowColor = 'rgba(0,0,0,0.25)';
        ctx.shadowBlur = Math.max(4, radius * 0.06);
        ctx.shadowOffsetX = 2;
        ctx.shadowOffsetY = 2;

        ctx.beginPath();
        ctx.moveTo(needleLength * 0.9, 0);
        ctx.lineTo(needleLength * 0.1, -needleWidth);
        ctx.lineTo(needleLength * 0.1, needleWidth);
        ctx.closePath();
        ctx.fillStyle = '#1f2937';
        ctx.fill();

        ctx.restore();

        // small cap already drawn by drawGauge center cap
    }

    function createGauge(canvas, value, opts) {
        opts = Object.assign({ greenMax: 2, yellowMax: 5, gaugeMinMaxPad: 2 }, opts || {});
        const gaugeMax = Math.max(8, Math.ceil(value + (opts.gaugeMinMaxPad || 2)));
        const targetProgress = Math.min(1, value / gaugeMax);

        function render(progress) {
            const state = drawGauge(canvas, value, Object.assign({}, opts, { gaugeMax: gaugeMax }));
            drawNeedle(state, progress, opts);
            // draw center value after needle so it stays visible on top
            drawCenterValue(state, value, Object.assign({}, opts, { gaugeMax: gaugeMax }), progress);
        }

        // animate needle
        const duration = opts.duration || 900;
        let start = null;
        function step(ts) {
            if (!start) start = ts;
            const elapsed = ts - start;
            const t = Math.min(1, elapsed / duration);
            // easeOutCubic
            const eased = 1 - Math.pow(1 - t, 3);
            const prog = eased * targetProgress;
            render(prog);
            if (t < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);

        // expose a resize handler
        return {
            resize: function() { render(targetProgress); }
        };
    }

    // Initialize all canvases with id 'speedometerChart'
    function initAll() {
        const canvases = document.querySelectorAll('#speedometerChart');
        canvases.forEach(function(c) {
            const raw = parseFloat(c.getAttribute('data-speed-value')) || 0;
            const gauge = createGauge(c, raw, {
                greenMax: 2,
                yellowMax: 5,
                duration: 900,
                lineWidthRatio: 0.18,
                needleWidthRatio: 0.06,
                // center font reduced by ~50% from previous default
                centerFontRatio: 0.225,
                // dynamic placement to the right of needle
                centerValuePosition: 'right',
                // tuning for right placement
                centerValueLabelDistanceRatio: 0.6,
                centerValuePerpOffsetRatio: 0.22,
                centerYRatio: 0.82,
                radiusRatio: 0.36
            });

            // debounced resize
            let t;
            window.addEventListener('resize', function() {
                clearTimeout(t);
                t = setTimeout(function() { gauge.resize(); }, 150);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

})();
