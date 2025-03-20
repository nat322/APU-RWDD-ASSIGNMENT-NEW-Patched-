<?php 
session_start();

if (!isset($_SESSION['user_id'])) {
    // Destroy any session that exists and force logout
    session_destroy();
    header("Location: index.php"); // Redirect to login page
    exit;
}
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced TF-IDF + TextRank Summarizer</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://unpkg.com/compromise"></script>
    <link rel="stylesheet" href="sidebar.css">
    <script src="sidebar.js" defer></script>
    <script src="notification.js"></script>
    <link rel="stylesheet" href="notification.css">
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        :root {
            --primary: #1f2937;
            --primary-light: #374151;
            --primary-lighter: #4B5563;
            --accent: #6366F1;
            --text: #F9FAFB;
            --text-light: #D1D5DB;
            --light-bg: #F3F4F6;
            --border: #6B7280;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            color: var(--primary);
            margin: 0;
            padding: 0;
            line-height: 1.6;
            display: flex;
            overflow: hidden;
            height: 100vh;
        }
        
        .container {
            display: flex;
            min-height: 100vh;
            width: 100%;
            overflow: hidden;
        }
        
        .header {
            background-color: var(--primary);
            color: var(--text);
            padding: 14px;
            align-items: center;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            text-align: center;
        }
        
        .subtitle {
            color: var(--text-light);
            font-size: 16px;
            margin-top: 8px;
            text-align: center;
        }
        
        .upload-section {
            background-color: #252f3f;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin-right: 15px;
        }
        
        .file-input {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-input-label {
            display: inline-block;
            padding: 10px 20px;
            background-color: var(--primary-light);
            color: var(--text);
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-input-label:hover {
            background-color: var(--primary);
        }
        
        .file-name {
            margin-top: 10px;
            font-size: 14px;
            color: white;
        }
        
        .summarize-btn {
            display: inline-block;
            background-color: var(--accent);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .summarize-btn:hover {
            background-color: #4F46E5;
            transform: translateY(-2px);
        }
        
        .summarize-btn:disabled {
            background-color: var(--text-light);
            cursor: not-allowed;
        }
        
        .result-section {
            background-color: #252f3f;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        
        .result-header {
            text-align: center;
            color: white;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--light-bg);
            margin-bottom: 15px;
        }
        
        .summary {
            white-space: pre-line;
            line-height: 1.7;
            color: var(--primary);
            background-color: var(--light-bg);
            padding: 15px;
            border-radius: 5px;
            min-height: 100px;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid var(--accent);
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .stats {
            display: flex;
            justify-content: space-between;
            background-color: var(--primary);
            color: var(--text);
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-light);
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                left: -220px; /* Initially hidden */
                width: 220px;
                height: 100vh;
                position: fixed;
                transition: left 0.3s ease-in-out;
            }
        
            .sidebar.open {
                left: 0; /* Sidebar slides into view */
            }
            
            .input-container {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        <div class="main-content">
        <div class="topbar">
                <div class="menu-icon">☰</div> <!-- Mobile menu toggle -->
                <div class="search-bar">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" placeholder="Search Time Leap...">
                </div>
                <div class="notification">
                    <span id="notification-icon">🔔</span>
                    <span id="notification-dot" class="hidden"></span>
                    <div id="notification-dropdown" class="hidden">
                        <div class="dropdown-header">Friend Requests</div>
                        <ul id="notification-list"></ul>
                    </div>
                </div>
            </div>

            <!-- Friend Request Overlay -->
            <div id="friend-request-overlay" class="hidden">
                <div class="overlay-content">
                    <h3>Friend Request</h3>
                    <p id="request-message"></p>
                    <button id="accept-request">Accept</button>
                    <button id="decline-request">Decline</button>
                    <button id="close-overlay">Close</button>
                </div>
            </div>

            <div class="header">
                <h1>TF-IDF + TextRank Summarizer</h1>
                <div class="subtitle">Intelligent document summarization powered by TextRank algorithm</div>
            </div>

            <div class="upload-section">
                <div class="file-input-wrapper">
                    <input type="file" id="docxFile" accept=".docx" class="file-input">
                    <label for="docxFile" class="file-input-label">Choose DOCX File</label>
                </div>
                <button id="summarizeBtn" class="summarize-btn" disabled>Summarize</button>
                <div id="fileName" class="file-name">No file selected</div>
            </div>
                
            <div class="loading" id="loadingSection">
                <div class="spinner"></div>
                <p>Analyzing document...</p>
            </div>
                
            <div class="result-section">
                <h3 class="result-header">Summary</h3>
                <div id="summary" class="summary">Your summary will appear here after processing.</div>
                    
                <div class="stats" id="statsSection" style="display: none;">
                    <div class="stat-item">
                        <div class="stat-value" id="originalWords">0</div>
                        <div class="stat-label">Original Words</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="summaryWords">0</div>
                        <div class="stat-label">Summary Words</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="compressionRate">0%</div>
                        <div class="stat-label">Compression Rate</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="entityCount">0</div>
                        <div class="stat-label">Key Entities</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle file selection UI updates
        document.getElementById("docxFile").addEventListener("change", function() {
            const fileInput = document.getElementById("docxFile");
            const fileName = document.getElementById("fileName");
            const summarizeBtn = document.getElementById("summarizeBtn");
            
            if (fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
                summarizeBtn.disabled = false;
            } else {
                fileName.textContent = "No file selected";
                summarizeBtn.disabled = true;
            }
        });
        
        // Initialize summarize button
        document.getElementById("summarizeBtn").addEventListener("click", summarizeDoc);
        
        async function summarizeDoc() {
            const fileInput = document.getElementById("docxFile");
            const loadingSection = document.getElementById("loadingSection");
            const statsSection = document.getElementById("statsSection");
            
            if (!fileInput.files.length) {
                alert("Please upload a .docx file!");
                return;
            }
            
            // Show loading animation
            loadingSection.style.display = "block";
            document.getElementById("summary").innerText = "Analyzing document...";
            statsSection.style.display = "none";
            
            try {
                let reader = new FileReader();
                reader.onload = async function(event) {
                    let arrayBuffer = event.target.result;
                    let text = await extractTextFromDocx(arrayBuffer);
                    let summary = await summarizeText(text);
                    
                    // Update the summary
                    document.getElementById("summary").innerText = summary;
                    
                    // Update stats
                    const originalWordCount = text.match(/\b\w+\b/g)?.length || 0;
                    const summaryWordCount = summary.match(/\b\w+\b/g)?.length || 0;
                    const compressionRate = originalWordCount > 0 
                        ? Math.round((1 - summaryWordCount / originalWordCount) * 100) 
                        : 0;
                    const entities = extractNamedEntities(text);
                    
                    document.getElementById("originalWords").innerText = originalWordCount;
                    document.getElementById("summaryWords").innerText = summaryWordCount;
                    document.getElementById("compressionRate").innerText = compressionRate + "%";
                    document.getElementById("entityCount").innerText = entities.size;
                    
                    // Show stats section
                    statsSection.style.display = "flex";
                };
                reader.readAsArrayBuffer(fileInput.files[0]);
            } catch (error) {
                document.getElementById("summary").innerText = "Error processing document: " + error.message;
            } finally {
                // Hide loading animation
                loadingSection.style.display = "none";
            }
        }

        async function extractTextFromDocx(arrayBuffer) {
            let zip = await JSZip.loadAsync(arrayBuffer);
            let xmlData = await zip.file("word/document.xml").async("text");

            let text = new DOMParser()
                .parseFromString(xmlData, "text/xml")
                .getElementsByTagName("w:t");

            return Array.from(text).map(node => node.textContent).join(" ").trim();
        }

        function splitSentences(text) {
            let doc = nlp(text);
            let sentences = doc.sentences().out('array');
            return sentences.length > 0 ? sentences : text.split(/(?<!\b\w{1,2})[.!?]\s+/);
        }

        function computeTFIDF(sentences) {
            let wordCounts = {};
            let totalWords = 0;
            let allWords = [];

            sentences.forEach(sentence => {
                let words = sentence.toLowerCase().match(/\b[a-z]+\b/g) || [];
                allWords.push(...words);
                words.forEach(word => {
                    wordCounts[word] = (wordCounts[word] || 0) + 1;
                });
                totalWords += words.length;
            });

            let tfidfScores = {};
            for (let word in wordCounts) {
                let tf = wordCounts[word] / totalWords;
                let idf = Math.log(sentences.length / (1 + wordCounts[word]));
                tfidfScores[word] = tf * idf;
            }

            return tfidfScores;
        }

        function getSentenceVectors(sentences, tfidfScores) {
            return sentences.map(sentence => {
                let words = sentence.toLowerCase().match(/\b[a-z]+\b/g) || [];
                return words.map(word => tfidfScores[word] || 0);
            });
        }

        function cosineSimilarity(vec1, vec2) {
            let dotProduct = vec1.reduce((sum, val, i) => sum + val * (vec2[i] || 0), 0);
            let magnitude1 = Math.sqrt(vec1.reduce((sum, val) => sum + val ** 2, 0));
            let magnitude2 = Math.sqrt(vec2.reduce((sum, val) => sum + val ** 2, 0));
            return magnitude1 * magnitude2 ? dotProduct / (magnitude1 * magnitude2) : 0;
        }

        function buildSimilarityMatrix(vectors) {
            let n = vectors.length;
            let matrix = Array.from({ length: n }, () => Array(n).fill(0));

            for (let i = 0; i < n; i++) {
                for (let j = 0; j < n; j++) {
                    if (i !== j) {
                        matrix[i][j] = cosineSimilarity(vectors[i], vectors[j]);
                    }
                }
            }

            return matrix;
        }

        function textRank(matrix, damping = 0.85, iterations = 50) {
            let n = matrix.length;
            let scores = Array(n).fill(1);

            for (let iter = 0; iter < iterations; iter++) {
                let newScores = Array(n).fill(1 - damping);

                for (let i = 0; i < n; i++) {
                    for (let j = 0; j < n; j++) {
                        if (matrix[j][i] > 0) {
                            let sumLinks = matrix[j].reduce((a, b) => a + b, 0);
                            newScores[i] += damping * (matrix[j][i] * scores[j] / sumLinks);
                        }
                    }
                }

                scores = newScores;
            }

            return scores;
        }

        async function summarizeText(text) {
            let sentences = splitSentences(text);
            if (sentences.length < 3) return sentences.join(" ");
        
            let entities = extractNamedEntities(text);
        
            let tfidfScores = computeTFIDF(sentences);
            let sentenceVectors = getSentenceVectors(sentences, tfidfScores);
            let similarityMatrix = buildSimilarityMatrix(sentenceVectors);
            let scores = textRank(similarityMatrix);
            
            let summarySentences = extractKeySentences(sentences, scores, entities);
        
            return summarySentences.join(" ");
        }        

        function extractNamedEntities(text) {
            let doc = nlp(text);
            let entities = new Set(
                doc.people().out('array')
                .concat(doc.places().out('array'))
                .concat(doc.organizations().out('array'))
            );
        
            // Also detect capitalized words as potential entities
            let capitalizedWords = text.match(/\b[A-Z][a-z]+\b/g) || [];
            capitalizedWords.forEach(word => entities.add(word));
        
            return entities;
        }        

        function extractKeySentences(sentences, scores, entities) {
            let rankedSentences = sentences.map((sentence, index) => ({
                index, sentence, score: scores[index]
            }));
        
            // Adjust scores for named entities
            rankedSentences.forEach(s => {
                for (let entity of entities) {
                    if (s.sentence.includes(entity)) {
                        s.score *= 1.2; // Boost score for key entities
                    }
                }
            });
        
            rankedSentences.sort((a, b) => b.score - a.score);
        
            let summary = [];
            let selectedIndexes = new Set();
            let k = Math.max(3, Math.ceil(sentences.length * 0.25));
        
            for (let s of rankedSentences) {
                if (summary.length >= k) break;
        
                let isSimilar = summary.some(prev =>
                    cosineSimilarity(getSentenceVectors([prev], computeTFIDF([prev]))[0], 
                    getSentenceVectors([s.sentence], computeTFIDF([s.sentence]))[0]) > 0.8
                );
        
                if (!isSimilar || entities.has(s.sentence)) {
                    summary.push(s.sentence);
                    selectedIndexes.add(s.index);
                }
            }
        
            // Ensure at least one entity sentence is present
            let entitySentences = rankedSentences.filter(s => [...entities].some(e => s.sentence.includes(e)));
            if (entitySentences.length > 0 && !summary.some(s => entitySentences.map(es => es.sentence).includes(s))) {
                summary.push(entitySentences[0].sentence);
            }
        
            return summary.sort((a, b) => sentences.indexOf(a) - sentences.indexOf(b));
        }        
    </script>
</body>
</html>