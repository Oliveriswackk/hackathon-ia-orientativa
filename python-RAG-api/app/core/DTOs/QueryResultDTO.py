from dataclasses import dataclass

@dataclass
class QueryResultDTO:
    id: str
    distance: int
    document: str
