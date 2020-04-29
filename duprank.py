import csv
from jinja2 import Template, FileSystemLoader, TemplateNotFound, Environment, select_autoescape
from os.path import join, exists, getmtime

with open('steve.csv') as csv_file:
    csv_reader = csv.reader(csv_file, delimiter=',')
    keynames = []
    results = {}
    pairs = {}
    ns = set()
    wantedKeys = ['board', 'score', 'ns_pair', 'ew_pair', 'north_username', 'south_username', 'east_username', 'west_username']
    for row in csv_reader:
        if not keynames:
            keynames = row
        else:
            board = {}
            for keyname, value in zip(keynames, row):
                if keyname in wantedKeys:
                    board[keyname] = value;
            boardNum = int(board['board'])
            board['score'] = int(board['score'])
            if boardNum not in results: results[boardNum] = []
            results[boardNum].append(board)
            pairs[board['ns_pair']] = board['north_username'],board['south_username']
            pairs[board['ew_pair']] = board['east_username'],board['west_username']
            ns.add(board['ns_pair'])
            for dirn in ['north', 'south', 'east', 'west']: del board[dirn + '_username']
           
for boardNum in results:
    played = len(results[boardNum])
    results[boardNum].sort(key=lambda board: board['score'],reverse=True)

for key in results.keys():
    resultsForBoard = results[key]
    index = 0
    best = resultsForBoard[index]['score']
    for seqnum in range(len(resultsForBoard)):
        board = resultsForBoard[seqnum]
        if resultsForBoard[seqnum]['score'] < best:
            for i in range(index, seqnum):
                resultsForBoard[i]['ns_points'] = 2*played - index-seqnum-1
                resultsForBoard[i]['ew_points'] = 2*played - 2 - resultsForBoard[i]['ns_points']
            index = seqnum
            best = resultsForBoard[index]['score']
    for i in range(index, len(resultsForBoard)):
        resultsForBoard[i]['ns_points'] = 2*played - index-len(resultsForBoard)-1
        resultsForBoard[i]['ew_points'] = 2*played - 2 - resultsForBoard[i]['ns_points']

    for board in resultsForBoard:
        board["ns_%"] = round(board['ns_points'] * 50 / (len(resultsForBoard)-1),2)
        board["ew_%"] = round(board['ew_points'] * 50 / (len(resultsForBoard)-1),2)

rankings = {}
for i in range(len(pairs)):
    rankings[str(i+1)] = {'points':0} 

for key in results.keys():
    resultsForBoard = results[key]
    for board in resultsForBoard:
        rankings[board['ns_pair']]['points'] +=  board['ns_points']
        rankings[board['ew_pair']]['points'] +=  board['ew_points']

rankingTable = []
tops = len(results) * (2* played -2)
for key in rankings.keys():
    ranking = rankings[key]
    ranking['%'] = ranking['points'] * 100. / tops
    ranking['pair'] = key
    ranking['name'] = pairs[key]
    ranking['tops'] = tops
    rankingTable.append(ranking)

rankingTable.sort(key = lambda pair: pair['%'], reverse=True)

rankingTableNS = []
rankingTableEW = []
for ranking in rankingTable:
    if ranking['pair'] in ns:
        rankingTableNS.append(ranking)
    else:
        rankingTableEW.append(ranking)

for rankingTable in rankingTableNS,rankingTableEW:

    pos = 0
    oldScore = rankingTable[0]["%"]
    for i in range (len(rankingTable)):
        ranking =  rankingTable[i]            
        if ranking["%"] != oldScore:
            scoreText = str(pos+1)
            if i != pos + 1: scoreText += "="
            for j in range(pos,i):
                rankingTable[j]["pos"] = scoreText
            oldScore = ranking["%"]
            pos = i
    scoreText = str(pos+1)
    if len(rankingTable) != pos + 1: scoreText += "="
    for j in range(pos,len(rankingTable)):
        rankingTable[j]["pos"] = scoreText
            
    for ranking in rankingTable:
        ranking["%"] = round(ranking['%'],2)
        print (ranking)     
                  
env = Environment(
loader=FileSystemLoader('.'),
autoescape=select_autoescape(['html'])
)
template = env.get_template('duprank.html')
print(template.render(title='Results from Online Gentle Duplicate', rankingNS=rankingTableNS, rankingEW=rankingTableEW))
            
   
        
            
          
   
