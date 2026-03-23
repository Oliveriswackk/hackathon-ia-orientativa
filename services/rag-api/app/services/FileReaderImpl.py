import fitz
from app.core.interfaces.IFileReader import IFileReader


class FileReaderImpl(IFileReader):

    @staticmethod
    def readPDF(self, path_pdf)-> str:
        doc = fitz.open(path_pdf)
        text = ""
        for pagina in doc:
            text += pagina.get_text()
        return (text)

    @staticmethod
    def readTextfile(self, path_textfile)-> str:
        with open(path_textfile, "r", encoding="utf-8") as f:
            text = f.read()
        return (text)
