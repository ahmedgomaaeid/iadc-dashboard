

    // Fetch and update leaderboard
    async function fetchLeaderboard() {
        try {
            const response = await fetch(`/api/quizzes/${QUIZ_ID}/leaderboard`);
            const data = await response.json();

            if (data.success) {
                updateLeaderboard(data.leaderboard, data.total_participants);
                updateLastUpdateTime();
            }
        } catch (error) {
            console.error('Error fetching leaderboard:', error);
        }
    }

    function updateLeaderboard(leaderboard, totalParticipants) {
        const tbody = document.getElementById('leaderboard-body');

        // Store full data for modal access
        if (typeof allLeaderboardData !== 'undefined') {
            allLeaderboardData = leaderboard;
        }

        // Update stats
        document.getElementById('total-participants').textContent = totalParticipants;
        document.getElementById('top-score').textContent = leaderboard.length > 0 ? leaderboard[0].score : 0;

        if (leaderboard.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-users-slash fa-2x mb-3"></i>
                        <p>No participants yet. Waiting for quiz takers...</p>
                    </td>
                </tr>
            `;
            previousLeaderboard = [];
            return;
        }

        // Build new rows with animation classes
        const rows = leaderboard.map((entry, index) => {
            const previous = previousLeaderboard.find(p => p.participant_id === entry.participant_id);
            let animationClass = '';

            if (!previous) {
                animationClass = 'new-entry';
            } else if (previous.rank > entry.rank) {
                animationClass = 'position-up';
            } else if (previous.rank < entry.rank) {
                animationClass = 'position-down';
            } else if (previous.score !== entry.score) {
                animationClass = 'score-changed';
            }

            const rankClass = entry.rank === 1 ? 'rank-1' :
                             entry.rank === 2 ? 'rank-2' :
                             entry.rank === 3 ? 'rank-3' : 'rank-other';

            const rankIcon = entry.rank === 1 ? '<i class="fas fa-crown"></i>' :
                            entry.rank === 2 ? '<i class="fas fa-medal"></i>' :
                            entry.rank === 3 ? '<i class="fas fa-award"></i>' : entry.rank;

            const initials = entry.name.split(' ')
                .map(word => word[0])
                .join('')
                .toUpperCase()
                .substring(0, 2);

            const statusColor = entry.score > 0 ? 'success' : 'secondary';
            const statusText = entry.score > 0 ? 'Active' : 'Joined';

            // Violation badge
            const vCount = entry.violations_count || 0;
            let violationHtml = '';
            if (vCount === 0) {
                violationHtml = `<span class="violation-badge violation-clean"><i class="fas fa-check-circle"></i> Clean</span>`;
            } else if (vCount <= 2) {
                violationHtml = `<span class="violation-badge violation-minor" onclick="showViolationDetails('${entry.participant_id}')" title="Click for details"><i class="fas fa-exclamation-triangle"></i> ${vCount} Minor</span>`;
            } else {
                violationHtml = `<span class="violation-badge violation-suspicious" onclick="showViolationDetails('${entry.participant_id}')" title="Click for details"><i class="fas fa-skull-crossbones"></i> ${vCount} Suspicious</span>`;
            }

            return `
                <tr class="leaderboard-row ${animationClass}" data-participant-id="${entry.participant_id}">
                    <td class="text-center align-middle">
                        <span class="rank-badge ${rankClass}">
                            ${rankIcon}
                        </span>
                    </td>
                    <td class="align-middle">
                        <div class="participant-info">
                            <div class="participant-avatar">${initials}</div>
                            <div>
                                <div class="fw-bold">${escapeHtml(entry.name)}</div>
                                <small class="text-muted">${escapeHtml(entry.email)}</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-center align-middle">
                        <span class="score-badge">${entry.score}</span>
                    </td>
                    <td class="text-center align-middle">
                        ${violationHtml}
                    </td>
                    <td class="text-center align-middle">
                        <span class="status-badge bg-${statusColor} text-white">
                            <i class="fas fa-circle-check me-1"></i>${statusText}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.innerHTML = rows;
        previousLeaderboard = leaderboard;

        // Remove animation classes after animation completes
        setTimeout(() => {
            document.querySelectorAll('.leaderboard-row').forEach(row => {
                row.classList.remove('new-entry', 'position-up', 'position-down', 'score-changed');
            });
        }, 500);
    }

    // Show violation details in modal
    function showViolationDetails(participantId) {
        if (typeof allLeaderboardData === 'undefined') return;

        const participant = allLeaderboardData.find(p => p.participant_id === participantId);
        if (!participant) return;

        document.getElementById('violation-participant-name').textContent = participant.name + ' — ' + participant.violations_count + ' violation(s)';

        const violations = participant.violations || [];
        if (violations.length === 0) {
            document.getElementById('violation-details-list').innerHTML = '<p class="text-muted text-center">No violation details available.</p>';
        } else {
            let html = '';
            violations.forEach((v, idx) => {
                const isFullscreen = v.type === 'fullscreen_exit';
                const typeIcon = isFullscreen ? 'fa-expand' : 'fa-window-restore';
                const typeClass = isFullscreen ? 'violation-type-fullscreen' : 'violation-type-tab';
                const typeLabel = isFullscreen ? 'Fullscreen Exit' : 'Tab Switch';
                const timeStr = v.recorded_at || new Date(v.timestamp * 1000).toLocaleString();
                const qNum = v.question_number ? `Question #${v.question_number}` : 'N/A';

                html += `
                    <div class="violation-item">
                        <div class="violation-type-icon ${typeClass}">
                            <i class="fas ${typeIcon}"></i>
                        </div>
                        <div>
                            <div class="fw-bold">#${idx + 1} — ${typeLabel}</div>
                            <small class="text-muted">${timeStr} • ${qNum}</small>
                        </div>
                    </div>
                `;
            });
            document.getElementById('violation-details-list').innerHTML = html;
        }

        const modal = new bootstrap.Modal(document.getElementById('violationModal'));
        modal.show();
    }

    function updateLastUpdateTime() {
        document.getElementById('last-update').textContent = 'Just now';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Start auto-refresh
    function startAutoRefresh() {
        fetchLeaderboard(); // Initial fetch
        updateTimer = setInterval(fetchLeaderboard, UPDATE_INTERVAL);
    }

    // Stop auto-refresh when page is hidden
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            clearInterval(updateTimer);
        } else {
            startAutoRefresh();
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        clearInterval(updateTimer);
    });

    // Initialize
    startAutoRefresh();