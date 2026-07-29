import {createTheme} from "@mui/material";

export const CustomTheme = createTheme({
  palette: {
      mode: 'dark',
      background: {
          paper: '#0e1113',
      },
      text: {
          primary: '#ffffff',
          secondary: '#aaaaaa',
      },
      primary: {
          main: '#04b4cc',
      },
      secondary: {
          main: '#a92424',
      },
      border: {
          primary: '#282828',
      }
  },
  typography: {
      fontFamily: 'inherit',
      button: {
          textTransform: 'none',
          color: '#000'
      },
      allVariants: {
          fontFamily: 'inherit',
          color: '#fff'
      },
  },
  components: {
    MuiFormControlLabel: {
      styleOverrides: {
        label: {
          padding: 0,
        },
      },
    },
    MuiPopover: {
        styleOverrides: {
            root: {
                zIndex: 130000,
            }
        }
    },
    MuiSnackbar: {
        styleOverrides: {
            root: {
                zIndex: 131000,
            }
        }
    },
    MuiButton: {
        styleOverrides: {
            root: {
                fontFamily: 'inherit',
            },
        }
    },
  }
});
