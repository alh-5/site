// Google Apps Script for Lawsohtun Voting System
// Deploy as a web app with "Execute as: Me" and "Who has access: Anyone"

const SHEET_NAMES = {
  VOTES: 'Votes',
  CANDIDATES: 'Candidates',
  VOTING_TIME: 'VotingTime',
  CONFIG: 'Config'
};

function doOptions() {
  return ContentService
    .createTextOutput(JSON.stringify({}))
    .setMimeType(ContentService.MimeType.JSON)
    .addHeader('Access-Control-Allow-Origin', '*')
    .addHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
    .addHeader('Access-Control-Allow-Headers', 'Content-Type');
}

// Cloudinary Configuration (Optional - Remove if not using Cloudinary)
const CLOUDINARY_CONFIG = {
  cloudName: 'dthv5mli2',
  apiKey: '279615986914929',
  apiSecret: '5MVyJSyITJj_K_dAgRBu17QnUFg',
  uploadPreset: 'ml_default' // Create "unsigned" upload preset in Cloudinary
};

function doGet(e) {
  const action = e.parameter.action;
  let result;
  
  try {
    switch (action) {
      case 'test':
        result = handleTest();
        break;
      case 'getAnalytics':
        result = handleGetAnalytics();
        break;
      case 'getCandidates':
        result = handleGetCandidates();
        break;
      case 'getVotingTime':
        result = handleGetVotingTime();
        break;
      case 'checkDuplicate':
        result = handleCheckDuplicate(e.parameter);
        break;
      case 'getVotes':
        result = handleGetVotes();
        break;
      default:
        result = { success: false, message: 'Invalid action' };
    }
  } catch (error) {
    result = { success: false, message: error.toString() };
  }
  
  return ContentService
    .createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}

function doPost(e) {
  const action = e.parameter.action;
  let result;
  
  try {
    switch (action) {
      case 'saveVote':
        result = handleSaveVote(e);
        break;
      case 'addCandidate':
        result = handleAddCandidate(e);
        break;
      case 'setVotingTime':
        result = handleSetVotingTime(e);
        break;
      default:
        result = { success: false, message: 'Invalid action' };
    }
  } catch (error) {
    result = { success: false, message: error.toString() };
  }
  
  return ContentService
    .createTextOutput(JSON.stringify(result))
    .setMimeType(ContentService.MimeType.JSON);
}

function handleTest() {
  setupSheets();
  const votesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.VOTES);
  const candidatesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.CANDIDATES);
  
  const totalVotes = votesSheet ? Math.max(votesSheet.getLastRow() - 1, 0) : 0;
  const totalCandidates = candidatesSheet ? Math.max(candidatesSheet.getLastRow() - 1, 0) : 0;
  
  return {
    success: true,
    data: {
      totalVotes: totalVotes,
      totalCandidates: totalCandidates,
      cloudinaryConfigured: false // Set to true if you configure Cloudinary
    }
  };
}

function handleGetAnalytics() {
  setupSheets();
  const votesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.VOTES);
  const candidatesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.CANDIDATES);
  const votingTimeSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.VOTING_TIME);
  
  const totalVotes = votesSheet ? Math.max(votesSheet.getLastRow() - 1, 0) : 0;
  const totalCandidates = candidatesSheet ? Math.max(candidatesSheet.getLastRow() - 1, 0) : 0;
  
  // Get voting activity status
  let votingActivity = 'Not configured';
  if (votingTimeSheet && votingTimeSheet.getLastRow() > 1) {
    const votingTime = votingTimeSheet.getRange(2, 1, 1, 2).getValues()[0];
    const startTime = new Date(votingTime[0]);
    const endTime = new Date(votingTime[1]);
    const now = new Date();
    
    if (now >= startTime && now <= endTime) {
      votingActivity = 'Active';
    } else if (now < startTime) {
      votingActivity = 'Not started';
    } else {
      votingActivity = 'Ended';
    }
  }
  
  // Get candidate votes
  const candidateVotes = {};
  if (votesSheet && totalVotes > 0) {
    const votes = votesSheet.getRange(2, 10, totalVotes, 1).getValues(); // Column J = Candidate Name
    for (let i = 0; i < votes.length; i++) {
      const candidate = votes[i][0];
      if (candidate) {
        candidateVotes[candidate] = (candidateVotes[candidate] || 0) + 1;
      }
    }
  }
  
  // Find top candidate
  let topCandidate = 'No votes yet';
  let maxVotes = 0;
  for (const [candidate, votes] of Object.entries(candidateVotes)) {
    if (votes > maxVotes) {
      maxVotes = votes;
      topCandidate = candidate;
    }
  }
  
  return {
    success: true,
    data: {
      totalVotes: totalVotes,
      totalCandidates: totalCandidates,
      votingActivity: votingActivity,
      candidateVotes: candidateVotes,
      topCandidate: topCandidate
    }
  };
}

function handleGetCandidates() {
  setupSheets();
  const candidatesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.CANDIDATES);
  const candidates = [];
  
  if (candidatesSheet && candidatesSheet.getLastRow() > 1) {
    const data = candidatesSheet.getRange(2, 1, candidatesSheet.getLastRow() - 1, 4).getValues();
    data.forEach(row => {
      candidates.push({
        id: row[0],
        name: row[1],
        bio: row[2],
        image: row[3]
      });
    });
  }
  
  return { success: true, data: candidates };
}

function handleGetVotingTime() {
  setupSheets();
  const votingTimeSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.VOTING_TIME);
  let votingTime = { start: null, end: null };
  
  if (votingTimeSheet && votingTimeSheet.getLastRow() > 1) {
    const data = votingTimeSheet.getRange(2, 1, 1, 2).getValues()[0];
    votingTime = { start: data[0], end: data[1] };
  }
  
  return { success: true, data: votingTime };
}

function handleCheckDuplicate(params) {
  setupSheets();
  const votesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.VOTES);
  
  if (!votesSheet || votesSheet.getLastRow() <= 1) {
    return { isDuplicate: false };
  }
  
  const data = votesSheet.getRange(2, 3, votesSheet.getLastRow() - 1, 3).getValues(); // Name, Age, EPIC
  
  for (let i = 0; i < data.length; i++) {
    const [name, age, epic] = data[i];
    if (name === params.name && age == params.age && epic === params.epic) {
      return {
        isDuplicate: true,
        reason: "Duplicate voter found with same name, age, and EPIC number."
      };
    }
  }
  
  return { isDuplicate: false };
}

function handleGetVotes() {
  setupSheets();
  const votesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.VOTES);
  const votes = [];
  
  if (votesSheet && votesSheet.getLastRow() > 1) {
    const data = votesSheet.getRange(2, 1, votesSheet.getLastRow() - 1, 14).getValues();
    data.forEach(row => {
      votes.push({
        timestamp: row[0],
        receiptId: row[1],
        name: row[2],
        age: row[3],
        epic: row[4],
        address: row[5],
        contactType: row[6],
        contact: row[7],
        candidateId: row[8],
        candidateName: row[9],
        epicDocumentUrl: row[10],
        selfieUrl: row[11],
        votingTimeStart: row[12],
        votingTimeEnd: row[13]
      });
    });
  }
  
  return { success: true, data: votes };
}

function handleSaveVote(e) {
  setupSheets();
  const params = e.parameter;
  const votesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.VOTES);
  
  // Upload to Cloudinary if configured (optional)
  let epicDocumentUrl = '';
  let selfieUrl = '';
  
  if (params.epicDocumentBase64 && CLOUDINARY_CONFIG.cloudName !== 'YOUR_CLOUDINARY_CLOUD_NAME') {
    epicDocumentUrl = uploadToCloudinary(params.epicDocumentBase64, 'epic_documents');
  }
  
  if (params.selfieBase64 && CLOUDINARY_CONFIG.cloudName !== 'YOUR_CLOUDINARY_CLOUD_NAME') {
    selfieUrl = uploadToCloudinary(params.selfieBase64, 'selfies');
  }
  
  // Append vote to sheet
  const newRow = [
    new Date(),
    params.receiptId,
    params.name,
    params.age,
    params.epic,
    params.address,
    params.contactType,
    params.contact,
    params.candidateId,
    params.candidateName,
    epicDocumentUrl,
    selfieUrl,
    params.votingTimeStart || '',
    params.votingTimeEnd || ''
  ];
  
  votesSheet.appendRow(newRow);
  const recordId = votesSheet.getLastRow();
  
  return {
    success: true,
    message: 'Vote saved successfully',
    data: {
      recordId: recordId,
      epicDocumentUrl: epicDocumentUrl,
      selfieUrl: selfieUrl
    }
  };
}

function handleAddCandidate(e) {
  setupSheets();
  const params = e.parameter;
  const candidatesSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.CANDIDATES);
  
  // Upload to Cloudinary if configured (optional)
  let imageUrl = '';
  if (params.imageBase64 && CLOUDINARY_CONFIG.cloudName !== 'YOUR_CLOUDINARY_CLOUD_NAME') {
    imageUrl = uploadToCloudinary(params.imageBase64, 'candidates');
  }
  
  // Append candidate to sheet
  const newRow = [
    params.id,
    params.name,
    params.bio || '',
    imageUrl
  ];
  
  candidatesSheet.appendRow(newRow);
  
  return {
    success: true,
    message: 'Candidate added successfully'
  };
}

function handleSetVotingTime(e) {
  setupSheets();
  const params = e.parameter;
  const votingTimeSheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName(SHEET_NAMES.VOTING_TIME);
  
  // Clear existing voting time and set new one
  votingTimeSheet.clear();
  votingTimeSheet.appendRow(['Start Time', 'End Time']);
  votingTimeSheet.appendRow([params.start, params.end]);
  
  return {
    success: true,
    message: 'Voting time set successfully'
  };
}

function setupSheets() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  
  // Create Votes sheet if it doesn't exist
  let sheet = ss.getSheetByName(SHEET_NAMES.VOTES);
  if (!sheet) {
    sheet = ss.insertSheet(SHEET_NAMES.VOTES);
    sheet.appendRow([
      'Timestamp', 'Receipt ID', 'Name', 'Age', 'EPIC Number', 'Address', 
      'Contact Type', 'Contact', 'Candidate ID', 'Candidate Name',
      'EPIC Document URL', 'Selfie URL', 'Voting Time Start', 'Voting Time End'
    ]);
  }
  
  // Create Candidates sheet if it doesn't exist
  sheet = ss.getSheetByName(SHEET_NAMES.CANDIDATES);
  if (!sheet) {
    sheet = ss.insertSheet(SHEET_NAMES.CANDIDATES);
    sheet.appendRow(['ID', 'Name', 'Bio', 'Image URL']);
  }
  
  // Create VotingTime sheet if it doesn't exist
  sheet = ss.getSheetByName(SHEET_NAMES.VOTING_TIME);
  if (!sheet) {
    sheet = ss.insertSheet(SHEET_NAMES.VOTING_TIME);
    sheet.appendRow(['Start Time', 'End Time']);
  }
  
  // Create Config sheet if it doesn't exist
  sheet = ss.getSheetByName(SHEET_NAMES.CONFIG);
  if (!sheet) {
    sheet = ss.insertSheet(SHEET_NAMES.CONFIG);
    sheet.appendRow(['Key', 'Value']);
    sheet.appendRow(['System Name', 'Lawsohtun Voting System']);
    sheet.appendRow(['Version', '1.0']);
  }
}

// Cloudinary Upload Function (Optional)
function uploadToCloudinary(base64Data, folder) {
  try {
    // Remove data:image/... prefix if present
    const base64String = base64Data.split(',')[1] || base64Data;
    
    const url = `https://api.cloudinary.com/v1_1/${CLOUDINARY_CONFIG.cloudName}/upload`;
    
    const payload = {
      'file': `data:image/jpeg;base64,${base64String}`,
      'upload_preset': CLOUDINARY_CONFIG.uploadPreset,
      'folder': folder
    };
    
    const options = {
      'method': 'post',
      'payload': payload
    };
    
    const response = UrlFetchApp.fetch(url, options);
    const result = JSON.parse(response.getContentText());
    
    return result.secure_url;
  } catch (error) {
    console.error('Cloudinary upload error:', error);
    return '';
  }
}